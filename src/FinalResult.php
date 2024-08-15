<?php

declare(strict_types=1);

class FinalResult {

    private const EXPECTED_ROW = 16;

    private const HEADER_ROW = 3;

    private const UNIT = 100;

    private string $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public  function results(): array
    {
        try {
            if (!file_exists($this->filePath)) {
                throw new Exception("File does not exist: " . $this->filePath);
            }

            $fileHandle = fopen($this->filePath, "r");
            if (!$fileHandle) {
                throw new Exception("Failed to open file: " . $this->filePath);
            }

            $headers = fgetcsv($fileHandle);
            if ($headers === false || count($headers) < self::HEADER_ROW) {
                throw new Exception("Invalid file format or missing headers.");
            }

            $records = $this->processFile($fileHandle, $headers);
            fclose($fileHandle);

            return [
                "filename" => basename($this->filePath),
                "document" => $fileHandle,
                "failure_code" => $headers[1],
                "failure_message" => $headers[2],
                "records" => $records
            ];

        } catch (Exception $e) {
            return [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
    }

    private function processFile($fileHandle, array $headers): array
    {
        $records = [];

        while (($row = fgetcsv($fileHandle)) !== false) {
            if (count($row) === self::EXPECTED_ROW) {
                $amount = !empty($row[8]) && $row[8] !== "0" ? (float) $row[8] : 0;
                $bankAccountNumber = !empty($row[6]) ? (int) $row[6] : "Bank account number missing";
                $bankBranchCode = !empty($row[2]) ? $row[2] : "Bank branch code missing";
                $endToEndId = !empty($row[10]) || !empty($row[11]) ? $row[10] . $row[11] : "End to end id missing";

                $record = [
                    "amount" => [
                        "currency" => $headers[0],
                        "subunits" => (int) ($amount * self::UNIT)
                    ],
                    "bank_account_name" => str_replace(" ", "_", strtolower($row[7])),
                    "bank_account_number" => $bankAccountNumber,
                    "bank_branch_code" => $bankBranchCode,
                    "bank_code" => $row[0],
                    "end_to_end_id" => $endToEndId,
                ];

                $records[] = $record;
            }
        }

        return $records;
    }
}

?>
