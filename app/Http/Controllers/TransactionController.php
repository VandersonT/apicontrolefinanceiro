<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*--------Models--------*/
use App\Models\Transaction;
/*----------------------*/

class TransactionController extends Controller{
    
    public function getUserTransactions(Request $request){
        $array = ['error' => ''];

        $transactions = Transaction::
            where('userId', $request->id)
            ->orderBy('id', 'DESC')
        ->simplePaginate(30);

        $array['transactions'] = $transactions;
        return $array;
    }
    
    public function getUserFinancialInfo(Request $request){
        $array = ['error' => ''];
        $SavedValue = 0;
        $netValue = 0;
        $totalTransitions = Transaction::where('userId', $request->id)->count();

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
        $array['totalTransitions'] = $totalTransitions;

        return $array;
    }

    public function sendNewTransition(Request $request){
        $array = ['error' => ''];

        $userId = filter_var($request->userId, FILTER_SANITIZE_STRING);
        $total = filter_var($request->total, FILTER_SANITIZE_STRING);
        $description = filter_var($request->description, FILTER_SANITIZE_STRING);
        $date = filter_var($request->date, FILTER_SANITIZE_STRING);
        $takenFrom = filter_var($request->takenFrom, FILTER_SANITIZE_STRING);
        $savedValue = filter_var($request->savedValue, FILTER_SANITIZE_STRING);
        $netValue = filter_var($request->netValue, FILTER_SANITIZE_STRING);


        if(!$userId || !$total || !$description || !$date){
            $array['error'] = 'Esta faltando algun(s) campo(s) obrigatório(s), confira e tente novamente.';
            return $array;
        }

        if($total < 0 && !$takenFrom){
            $array['error'] = 'Para retirar um valor informe de onde irá tira-lo, se do valor [disponivel] ou [Emergencial]';
            return $array;
        }

        if(!$netValue){
            $netValue = $total - $savedValue;
        }

        if($total >= 0){
            $takenFrom = '';
        }else{
            $savedValue = 0;
            $netValue = 0;
        }

        //all right! send to database now
        $newTransaction = new Transaction;
            $newTransaction->userId = $userId;
            $newTransaction->netValue = $netValue ? $netValue : 0;
            $newTransaction->savedValue = $savedValue ? $savedValue : 0;
            $newTransaction->total = $total;
            $newTransaction->description = $description;
            $newTransaction->date = $date;
            $newTransaction->takenFrom = $takenFrom ? $takenFrom : '';
        $newTransaction->save();

        $array['success'] = 'A transação foi realizada com sucesso!';

        return $array;
    }

    public function deleteTransition(Request $request){
        $array = ['error' => ''];

        $transitionToRemove = Transaction::find($request->id);
        $transitionToRemove->delete();

        return $array;
    }

}
