<?php

declare(strict_types=1);

function getTransactionFiles(string $dirPath): array
{
  $files = [];

  foreach (scandir($dirPath) as $file) {
    if (is_dir($file)) {
      continue;
    };

    $files[] = $dirPath . $file;
  };
  return $files;
};

function getTransactions(string $filePath, ?callable $transactionHandler = null): array
{
  if (!file_exists($filePath)) {
    trigger_error('File ' . $filePath . " does not exist.", E_USER_ERROR);
  }

  $file = fopen($filePath, 'r');

  fgetcsv($file); // moves the file pointer to the next line

  $transactions = [];

  while (($transaction = fgetcsv($file)) !== false) {
    if ($transactionHandler !== null) {
      $transaction = $transactionHandler($transaction);
    }
    $transactions[] = $transaction;
  }

  return $transactions;
}

function extractTransaction(array $transactionRow): array
{
  [$date, $checkNumber, $description, $amount] = $transactionRow;

  $amount = (float) str_replace(['$', ","], "", $amount);

  return [
    'date' => $date,
    'checkNumber' => $checkNumber,
    'description' => $description,
    'amount' => $amount,
  ];
}

function calculateTotals(array $transactions): array
{
  $netTotal = 0;
  $expenseTotal = 0;
  $incomeTotal = 0;

  foreach ($transactions as $transaction) {
    if (($amount = $transaction["amount"]) !== null) {
      if ($amount > 0) {
        $incomeTotal += $amount;
      } else {
        $expenseTotal += $amount;
      }
      $netTotal += $amount;
    }
  }
  return [
    "netTotal" => $netTotal,
    "expenseTotal" => $expenseTotal,
    "incomeTotal" => $incomeTotal,
  ];
}
