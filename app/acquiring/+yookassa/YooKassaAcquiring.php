<?php

use Psr\Log\LoggerInterface;
use YooKassa\Client;
use YooKassa\Model\ConfirmationAttributes\ConfirmationAttributesRedirect;
use YooKassa\Model\CurrencyCode;
use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\PaymentStatus;
use YooKassa\Model\Receipt\PaymentMode;
use YooKassa\Model\Receipt\PaymentSubject;
use YooKassa\Request\Payments\CreatePaymentRequest;

class YooKassaAcquiring implements Acquiring
{
    /** @var LoggerInterface */
    private $logger;

    /** @var Client */
    private $client;

    public function __construct()
    {
        $this->logger = YooKassaLog::get();
        $this->client = YooKassaClientFactory::create();
    }

    public function payForOrder(PaymentForOrder $purpose): ?string
    {
        return $this->pay(
            $purpose,
            $purpose->title(),
            $purpose->description(),
            $purpose->order->email,
            $purpose->order->phone
        );
    }

    private function pay(
        PaymentPurpose $purpose,
        string $title,
        string $description,
        string $email,
        string $phone
    ): ?string {
        $this->logger->info('Создание оплаты.', ['purpose' => $purpose->logData()]);

        try {
            if (! $purpose->needToPay() || $purpose->isPaid()) {
                return null;
            }

            $amount = $purpose->amountToPay();

            $confirmationAttribute = new ConfirmationAttributesRedirect();
            $confirmationAttribute->setReturnUrl($purpose->statusUrl());

            $requestBuilder = CreatePaymentRequest::builder()
                ->setAmount($amount, CurrencyCode::RUB)
                ->setCapture(true)
                ->setDescription(mb_substr($description, 0, 128))
                ->setTaxSystemCode(1)
                ->setConfirmation($confirmationAttribute);

            $request = $requestBuilder->build();

            $response = null;
            try {
                $response = $this->client->createPayment($request);
            } catch (Exception $e) {
                $this->logger->error('Не удалось создать оплату.', [
                    'exception' => $e,
                    'request' => $request,
                    'purpose' => $purpose->logData(),
                ]);
            }

            if ($response === null) {
                return null;
            }

            $responseConfirmation = $response->getConfirmation();
            if ($responseConfirmation !== null) {
                $redirectUrl = $responseConfirmation->getConfirmationUrl();
            } else {
                $redirectUrl = '';
            }

            $payment = $purpose->createPayment();
            $payment->type = Payment::YOOKASSA_TYPE;
            $payment->yookassa_code = $response->getId();
            $payment->yookassa_link = $redirectUrl;
            $payment->yookassa_status = $response->getStatus() ?? '';
            $payment->is_paid = $response->getPaid() ? 1 : 0;
            $payment->full_status = json_encode($response, JSON_UNESCAPED_UNICODE);

            $amount = $response->getAmount();
            if (isset($amount, $amount->value)) {
                $payment->sum = sprintf('%g', $amount->value);
            } else {
                $payment->sum = '';
            }

            $purpose->payment = $payment->save_and_load();

            return $redirectUrl;
        } catch (Exception $e) {
            $this->logger->error('Ошибка при попытке создать оплату.', [
                'exception' => $e,
                'purpose' => $purpose->logData(),
            ]);
            return null;
        }
    }

    public function update(string $code): ?PaymentPurpose
    {
        if ($code === '') {
            return null;
        }

        $payment = (new Payment())->find_by('yookassa_code', $code);
        $purpose = PaymentPurpose::createFromPayment($payment);
        if ($purpose === null) {
            $this->logger->error('Не удалось определить назначение платежа.', ['code' => $code]);
            return null;
        }

        if ($purpose->isCanceled() || $purpose->isPaid()) {
            return $purpose;
        }

        $this->processPaymentInfo($code, $purpose, [
            'code' => $code,
            'purpose' => $purpose->logData(),
        ]);
        return $purpose;
    }

    public function callback($source): bool
    {
        $this->logger->info('Обработка входящего уведомления.', ['source' => $source]);

        $purpose = null;
        try {
            $remoteIP = $_ENV['REMOTE_ADDR']; // TODO
            if (! $this->client->isNotificationIPTrusted($remoteIP)) {
                $this->logger->error('Запрос с не доверенного IP.', ['ip' => $remoteIP]);
                return false;
            }

            $data = json_decode($source, true);

            $notification = (new NotificationFactory())->factory($data);
            $response = $notification->getObject();
            if ($response === null) {
                $this->logger->error('Не удалось определить тип уведомления.', ['source' => $source]);
                return false;
            }

            $eventType = $notification->getEvent();
            if (
                $eventType !== NotificationEventType::PAYMENT_SUCCEEDED &&
                $eventType !== NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE &&
                $eventType !== NotificationEventType::PAYMENT_CANCELED
            ) {
                // другие события не интересуют, но не являются ошибкой
                return true;
            }

            $code = $response->getId();
            $payment = (new Payment())->find_by('yookassa_code', $code);

            $purpose = PaymentPurpose::createFromPayment($payment);
            if ($purpose === null) {
                $this->logger->error('Не удалось определить назначение платежа.', [
                    'source' => $source,
                    'code' => $code,
                ]);
                return false;
            }

            return $this->processPaymentInfo($code, $purpose, [
                'source' => $source,
                'code' => $code,
                'purpose' => $purpose->logData(),
            ]);
        } catch (Exception $e) {
            $this->logger->error(
                'Ошибка при обработке входящего уведомления.',
                [
                    'exception' => $e,
                    'source' => $source,
                    'code' => $code ?? null,
                    'purpose' => isset($purpose) ? $purpose->logData() : null,
                ]
            );
            return false;
        }
    }

    private function processPaymentInfo(string $code, PaymentPurpose $purpose, array $logData): bool
    {
        try {
            $payment = $purpose->payment;

            $paymentInfo = $this->client->getPaymentInfo($code);
            if ($paymentInfo === null) {
                $this->logger->error('Не удалось получить статус платежа.', $logData);
                return false;
            }

            $payment->full_status = json_encode($paymentInfo, JSON_UNESCAPED_UNICODE);

            $status = $paymentInfo->getStatus();
            if ($status === PaymentStatus::SUCCEEDED) {
                $payment->is_paid = 1;
            } elseif ($status === PaymentStatus::CANCELED) {
                $payment->is_canceled = 1;
            }
            $purpose->payment = $payment->save_and_load();

            $this->logger->info('Статус платежа обновлён.', $logData + ['payment' => $purpose->payment->to_array()]);

            if ($status === PaymentStatus::SUCCEEDED) {
                $purpose->onSuccess();
            }
            return true;
        } catch (Exception $e) {
            $this->logger->error(
                'Ошибка при обработке информации о платеже.',
                $logData + ['exception' => $e]
            );
            return false;
        }
    }
}