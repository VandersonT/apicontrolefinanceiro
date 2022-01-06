<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*--------Models--------*/
use App\Models\Transaction;
/*----------------------*/

class TransactionController extends Controller{
    public function getUserTransactions(Request $request){
        $array = ['error' => ''];
        $SavedValue = 0;
        $netValue = 0;

        $transactions = Transaction::where('userId', $request->id)->get();

        foreach($transactions as $transaction){
            
            if($transaction['total'] >= 0){
                $netValue = $netValue + $transaction['netValue'];
                $SavedValue = $SavedValue + $transaction['savedValue'];
            }else{
                if($transaction['takenFrom'] == 'Disponivel'){
                    $netValue = $netValue + $transaction['total'];//remembering that the value of "$transaction['total']" is negative
                }else{
                    $SavedValue = $SavedValue + $transaction['total'];//remembering that the value of "$transaction['total']" is negative
                }
            }
        }

        $array['netValueTotal'] = $netValue;
        $array['saveValueTotal'] = $SavedValue;
        $array['transactions'] = $transactions;

        return $array;
    }

    public function sendNewTransition(Request $request){

    }

}
