<?php
require_once __DIR__.'/core.php';
$txid=$_POST['cpm_trans_id'] ?? ($_POST['transaction_id'] ?? '');
if($txid){
    $chk=cinetpay_check($txid);
    $rech=read_json('bank_recharges.json',[]); $hit=false;
    foreach($rech as &$r){ if($r['txid']===$txid){ $hit=true; if($chk['accepted'] && $r['status']!=='credited'){ bank_credit($r['account_id'],$r['amount'],'Recharge '.$r['method']); $r['status']='credited'; } } } unset($r);
    if($hit) write_json('bank_recharges.json',$rech);
    if(!$hit){
        $orders=read_json('plan_orders.json',[]);
        foreach($orders as &$o){ if($o['txid']===$txid && $chk['accepted'] && $o['status']!=='paid'){ $o['status']='paid'; } } unset($o);
        write_json('plan_orders.json',$orders);
    }
}
http_response_code(200); echo 'OK';
