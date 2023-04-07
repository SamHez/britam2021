<?php

class Database {

    private $dsn = "mysql:host=localhost;dbname=clearbas_britam";
    private $user = "clearbas_root";
    private $passwd = "Skycode@2018";
    private $pdo;

    public function __construct(){
        $this->pdo = new PDO($this->dsn, $this->user, $this->passwd);
    }

    public function getAllTransactions(){
        $stm = $this->pdo->query("SELECT * FROM payment_transactions");
        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function savePaymentTransaction($data) {
        $sql = "INSERT INTO payment_transactions (referenceId, externalId, amount, currency, partyIdType, partyId, status) VALUES ('".$data['referenceId']."', '".$data['externalId']."', '".$data['amount']."', '".$data['currency']."', '".$data['payer']['partyIdType']."', '".$data['payer']['partyId']."', '".$data['status']."')";
        $statement = $this->pdo->exec($sql);
        return $this->pdo->lastInsertId();
        return $sql;
    }

    public function updatePayementStatus($referenceId, $status="PENDING") {
        return;
    }

}