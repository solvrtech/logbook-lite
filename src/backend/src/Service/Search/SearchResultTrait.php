<?php

namespace App\Service\Search;

use App\Common\CommonHelper;
use App\Common\DateTimeHelper;
use App\Entity\ResponseEntityInterface;
use DateTime;

trait SearchResultTrait
{
    /**
     * Modify item value in a multidimensional,
     * processing the array in batches to reduce memory usage.
     *
     * @param array $items
     * @param int $batchSize
     *
     * @return array
     */
    private function modifyItemsBatch(array $items, int $batchSize = 100): array
    {
        $result = [];

        // Process the data in batches
        foreach (array_chunk($items, $batchSize) as $batch) {
            // Process each batch using the datetimeToStr method
            $batchResult = $this->modifyItem($batch);

            // Merge the batch result with the final result array
            $result = array_merge($result, $batchResult);
        }

        return $result;
    }

    /**
     * Modify the item value.
     *
     * @param array $items
     *
     * @return array
     */
    private function modifyItem(array $items): array
    {
        foreach ($items as $key => $val) {
            if (is_array($val)) {
                $items[$key] = self::modifyItem($val);
            }

            if ($val instanceof ResponseEntityInterface) {
                $items[$key] = $val->toResponse();
            }

            if ($val instanceof DateTime) {
                $items[$key] = (new DateTimeHelper())->dateTimeToStr($val);
            }
        }

        return $items;
    }

    /**
     * Processes an array of items in batches, decoding JSON and formatting DateTime values.
     *
     * @param array $items
     * @param array $serializeKey
     * @param int $batchSize
     *
     * @return array
     */
    private function decodeAndFormatDataBatch(array $items, array $serializeKey, int $batchSize = 100): array
    {
        $result = [];

        // Process the data in batches
        foreach (array_chunk($items, $batchSize) as $batch) {
            // Process each batch using the datetimeToStr method
            $batchResult = $this->decodeAndFormatData($batch, $serializeKey);

            // Merge the batch result with the final result array
            $result = array_merge($result, $batchResult);
        }

        return $result;
    }

    /**
     * Takes an array of items and recursively decodes JSON data for specified keys
     * and formats any DateTime values in the array to a string representation.
     *
     * @param array $items
     * @param array $serializeKey The keys in the array whose values need to be decoded.
     *
     * @return array
     */
    private function decodeAndFormatData(array $items, array $serializeKey): array
    {
        // Convert the keys of the array to camelCase
        $items = (new CommonHelper())->arraySnakeToCamelCase($items);

        foreach ($items as $key => $val) {
            if (is_array($val)) {
                $items[$key] = self::decodeAndFormatData($val, $serializeKey);
            }

            if ($val instanceof DateTime) {
                $items[$key] = $this->formatDateTimeToString($val);
            }

            if (in_array($key, $serializeKey)) {
                $items[$key] = json_decode($val);
            }
        }

        return $items;
    }

    /**
     * Formats a DateTime value to a string representation.
     *
     * @param DateTime $dateTime
     * @param bool $withTime
     *
     * @return string
     */
    private function formatDateTimeToString(DateTime $dateTime, bool $withTime = true): string
    {
        return (new DateTimeHelper())->dateTimeToStr($dateTime, dateOnly: !$withTime);
    }
}