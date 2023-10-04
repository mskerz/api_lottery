<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;



$app->get('/lotteries', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $sql = 'select * from lottery';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
         
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});

 



$app->get('/lottery/search/{last3digit}/{draw_no}/{set_no}', function (Request $request, Response $response,$args){
    $last3digit = '%'.$args['last3digit'];
    $draw_no = $args['draw_no'];
    $set_no = $args['set_no'];	
    $conn = $GLOBALS['connect'];
    $sql = "select * from lottery where lottery_number like ? and draw_no = ? and set_no = ?";
    $stmt = $conn->prepare($sql);
    $stmt ->bind_param('sii', $last3digit,$draw_no,$set_no);
    $stmt-> execute();
    $result = $stmt->get_result();
    $data = array();
    while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
    }
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});
$app->get('/draw_set',function(Request $request,Response $response,$args){
    $conn = $GLOBALS['connect'];
    $sql = 'select * from draw_set';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);  
});
 

$app->post('/lottery/insert',function(Request $request, Response $response,$args){
    $jsonbody = $request->getBody();
    $data = json_decode($jsonbody,true);
    $lottery_number = $data['lottery_number'];
    $draw_no = $data['draw_no'];
    $set_no = $data['set_no'];
    $price = $data['price'];
    $quantity = $data['lottery_quantity'];
    
    
    $conn = $GLOBALS['connect'];
    $sql = 'insert into lottery (lottery_number,draw_no,set_no,price,lottery_quantity) values (?,?,?,?,?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siiii',$lottery_number,$draw_no,$set_no,$price,$quantity );
    $stmt->execute();
    // random number  000000 - 999999
    //return body เป็น json
    $affected=$stmt->affected_rows;
    if ($affected > 0) {

        $data = ["affected_rows" => $affected, "last_idx" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
});

$app->put('/lottery/edit/{idx}',function(Request $request, Response $response,$args){
    $idx = $args['idx'];
    $body = $request->getBody();
    $data = json_decode($body,true);
    $lottery_number = $data['lottery_number'];
    $draw_no = $data['draw_no'];
    $set_no = $data['set_no'];
    $price = $data['price'];
    $quantity = $data['lottery_quantity'];
    
    $conn = $GLOBALS['connect'];
    $sql = 'update lottery SET  lottery_number = ?,draw_no = ?,  set_no  = ?, price  = ?, lottery_quantity = ? where idx = ?';
    $stmt =$conn->prepare($sql);
    $stmt->bind_param('iiiiii',$lottery_number,$draw_no,$set_no,$price,$quantity,$idx);
    $stmt->execute();
    $response->getBody()->write(json_encode(["message" =>"Lottery Has been updated Successfully","Update_idx"=>$idx]));
    return $response;

});
$app->delete('/lottery/delete/{idx}',function(Request $request, Response $response,$args){
    $idx = $args['idx'];
    $conn = $GLOBALS['connect'];
    $sql = 'delete from lottery where idx = ?';
    $stmt =$conn->prepare($sql);
    $stmt->bind_param('i',$idx);
    $stmt->execute();
    $response->getBody()->write(json_encode(["message" =>"Lottery Has been Deleted Successfully","delete_idx"=>$idx]));
    return $response;
});


$app->get('/lottery/orders',function(Request $request, Response $response,$args){
    $conn = $GLOBALS['connect'];
    $sql = 'select * from lottery_order';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
       
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});
// $app->get('/lottery/order/details/{user_id}',function(Request $request, Response $response,$param){
//     $idx = $param['user_id'];
//     $conn  = $GLOBALS['connect'];
//     $sql  = 'SELECT lottery_order.purchase_date,member.fullname,lottery.lottery_number,
//             lottery_order.quantity_order,lottery.price as price_per_ticket,(lottery.price*lottery_order.quantity_order)as total_price 
//             from `lottery_order` INNER JOIN lottery ON lottery_order.lottery_idx = lottery.idx 
//             INNER JOIN member ON lottery_order.user_id = member.user_id 
//             WHERE member.user_id= ? ;';
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('i',$idx);
//     $stmt->execute();
//     $result =$stmt->get_result();
//     if($result->num_rows>0){
//         $data = array();
//         while($row = $result->fetch_assoc()){
//             array_push($data,$row);
//         }
//         $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//         return $response
//             ->withHeader('Content-Type', 'application/json; charset=utf-8')
//             ->withStatus(200);
//     }else {
//         // ไม่พบข้อมูลรายละเอียดการซื้อล็อตเตอรี่ของผู้ใช้คนนี้
//         $response->getBody()->write(json_encode(['message' => 'ไม่พบข้อมูลรายละเอียดการซื้อ']));
//         return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
//     }
    


// });
// $app->get('/landmark/{idx}', function (Request $request, Response $response, $args) {
//     $idx = $args['idx'];
//     $conn = $GLOBALS['connect'];
//     $sql = 'select landmark.idx, landmark.name,landmark.detail,landmark.url,
//     country.name as country from landmark inner join country on landmark.country = country.idx where landmark.idx = ?';
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param('i', $idx);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $data = [];
//     while ($row = $result->fetch_assoc()) {
//         array_push($data, $row);
//     }
//     $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
//     return $response
//         ->withHeader('Content-Type', 'application/json; charset=utf-8')
//         ->withStatus(200);
// });

 

$app->get('/lottery/{key}/{value}', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $keyword = $args['key'];
    $value = $args['value'];
    // ตรวจสอบคีย์เวิร์ดและกำหนดคิวรี่ SQL ตามคีย์เวิร์ดที่ระบุ
    $sql = 'select * from lottery where ';
    $bindType = '';

    if ($keyword === 'last3digit') {
        $sql .= 'lottery_number LIKE ?';
        $bindType = 's';
        $value = '%' . $value;
    } elseif ($keyword === 'draw_no') {
        $sql .= 'draw_no = ?';
        $bindType = 'i';
    } elseif ($keyword === 'set_no') {
        $sql .= 'set_no = ?';
        $bindType = 'i';
    } 
    $stmt = $conn->prepare($sql);
   
    $stmt->bind_param($bindType, $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array(); 
    foreach ($result as $row) {
        array_push($data, $row);
    }
     
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});