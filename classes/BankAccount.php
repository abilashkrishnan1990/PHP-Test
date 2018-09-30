<?php

class BankAccount implements IfaceBankAccount
{

    private $balance = null;

    public function __construct(Money $openingBalance){
        $this->balance = $openingBalance;
    }

    public function balance(){
        return $this->balance;
    }

    public function deposit(Money $amount){
        $amount  = $amount->value();
        $balance = $this->balance->value();
        $balance = $balance + $amount ;
        $this->balance = new Money($balance);
    }
    public function withdraw(Money $amount){
        //implement this method
    }
    public function transfer(Money $amount, BankAccount $account){
        //implement this method
    }
}