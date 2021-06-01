<?php

class CdekCitiesLoader
{
    /** @var array<CdekCity> */
    private $cities = [];

    public function run(): bool
    {
        $this->loadFromCdek();

        if (! $this->cities) {
            return false;
        }

        $this->saveData();

        $this->updateSubtitles();

        return true;
    }

    private function loadFromCdek(): void
    {
        $this->cities = d()->Cdek->cities();
    }

    private function saveData(): void
    {
        $import = new DBImport('cdek_cities');
        $import->query();
        foreach ($this->cities as $city) {
            $import->insert_or_update_row(
                'id',
                $city->code,
                [
                    'id' => $city->code,
                    'title' => $city->title,
                    'region' => $city->region,
                    'subregion' => $city->subregion,
                    'fias' => $city->fias,
                ]
            );
        }
        $import->do_updates();
        $import->do_inserts();
        $import->do_deletes();
    }

    private function updateSubtitles(): void
    {
        $notUniqueTitleIds = $this->selectGroupedIds(
            'SELECT GROUP_CONCAT(`id`) FROM `cdek_cities` GROUP BY `title` HAVING COUNT(*)>1'
        );

        if (! $notUniqueTitleIds) {
            d()->db->exec('update `cdek_cities` set `subtitle`=""');
            return;
        }

        d()->db->exec(
            sprintf(
                'update `cdek_cities` set `subtitle`="" where `id` not in (%s)',
                implode(',', array_map('e', $notUniqueTitleIds))
            )
        );

        $notUniqueRegionsIds = $this->selectGroupedIds(
            'SELECT GROUP_CONCAT(`id`) FROM `cdek_cities` GROUP BY `title`, `region` HAVING COUNT(*)>1'
        );

        $uniqueRegionsIds = array_diff($notUniqueTitleIds, $notUniqueRegionsIds);
        if ($uniqueRegionsIds) {
            d()->db->exec(
                sprintf(
                    'update `cdek_cities` set `subtitle`=`region` where `id` in (%s)',
                    implode(',', array_map('e', $uniqueRegionsIds))
                )
            );
        }

        if (! $notUniqueRegionsIds) {
            return;
        }

        d()->db->exec(
            sprintf(
                'update `cdek_cities` set `subtitle`=concat(`region`, ", ", `subregion`) where `id` in (%s)',
                implode(',', array_map('e', $notUniqueRegionsIds))
            )
        );
    }

    private function selectGroupedIds(string $query): array
    {
        $grouped_ids = d()->db
            ->query($query)
            ->fetchAll(PDO::FETCH_COLUMN, 0);
        $ids = [];
        foreach ($grouped_ids as $row) {
            foreach (explode(',', $row) as $id) {
                $ids[] = $id;
            }
        }
        return $ids;
    }
}