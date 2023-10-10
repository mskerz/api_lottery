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





$app->get('/lottery/search/{last3digit}/{draw_no}/{set_no}', function (Request $request, Response $response, $args) {
    $last3digit = '%' . $args['last3digit'];
    $draw_no = $args['draw_no'];
    $set_no = $args['set_no'];
    $conn = $GLOBALS['connect'];
    $sql = "select * from lottery where lottery_number like ? and draw_no = ? and set_no = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sii', $last3digit, $draw_no, $set_no);
    $stmt->execute();
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
$app->get('/draw_set', function (Request $request, Response $response, $args) {
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


$app->post('/lottery/insert', function (Request $request, Response $response, $args) {
    $jsonbody = $request->getBody();
    $data = json_decode($jsonbody, true);
    $lottery_number = $data['lottery_number'];
    $draw_no = $data['draw_no'];
    $set_no = $data['set_no'];
    $price = $data['price'];
    $quantity = $data['lottery_quantity'];


    $conn = $GLOBALS['connect'];
    $sql = 'insert into lottery (lottery_number,draw_no,set_no,price,lottery_quantity) values (?,?,?,?,?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siiii', $lottery_number, $draw_no, $set_no, $price, $quantity);
    $stmt->execute();
    // random number  000000 - 999999
    //return body เป็น json
    $affected = $stmt->affected_rows;
    if ($affected > 0) {

        $data = ["affected_rows" => $affected, "last_idx" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
});

$app->put('/lottery/edit/{idx}', function (Request $request, Response $response, $args) {
    $idx = $args['idx'];
    $body = $request->getBody();
    $data = json_decode($body, true);
    $lottery_number = $data['lottery_number'];
    $draw_no = $data['draw_no'];
    $set_no = $data['set_no'];
    $price = $data['price'];
    $quantity = $data['lottery_quantity'];

    $conn = $GLOBALS['connect'];
    $sql = 'update lottery SET  lottery_number = ?,draw_no = ?,  set_no  = ?, price  = ?, lottery_quantity = ? where idx = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiiiii', $lottery_number, $draw_no, $set_no, $price, $quantity, $idx);
    $stmt->execute();
    $response->getBody()->write(json_encode(["message" => "Lottery Has been updated Successfully", "Update_idx" => $idx]));
    return $response;

});
$app->delete('/lottery/delete/{idx}', function (Request $request, Response $response, $args) {
    $idx = $args['idx'];
    $conn = $GLOBALS['connect'];
    $sql = 'delete from lottery where idx = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idx);
    $stmt->execute();
    $response->getBody()->write(json_encode(["message" => "Lottery Has been Deleted Successfully", "delete_idx" => $idx]));
    return $response;
});


$app->get('/lottery/orders', function (Request $request, Response $response, $args) {
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
    $sql = 'select * from lottery where';
    $bindType = "";

    if ($keyword === 'last3digit') {
        $sql .= ' lottery_number LIKE ?';
        $bindType = "s";
        $value = '%' . $value;
    } elseif ($keyword === 'draw_no') {
        $sql .= ' draw_no = ? ';
        $bindType = "i";
    } elseif ($keyword === 'set_no') {
        $sql .= ' set_no = ? ';
        $bindType = "i";
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

/// ดู order ล่าสุดที่เราซื้อไป
$app->get('/last_order/{user_id}', function (Request $request, Response $response, $args) {
    $userId = $args['user_id'];

    // ค้นหาข้อมูลล่าสุดของคำสั่งซื้อโดยใช้ user_id
    $getLastOrderQuery = "SELECT
                            o.order_id,
                            o.purchase_date,
                            m.fullname
                        FROM
                            lottery_order o
                        INNER JOIN
                            member m ON o.user_id = m.user_id
                        WHERE
                            o.user_id = ?
                        ORDER BY
                            o.purchase_date DESC
                        LIMIT 1";
    $conn = $GLOBALS['connect'];
    $stmt = $conn->prepare($getLastOrderQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = [];
        $row = $result->fetch_assoc();
        $data['order_id'] = $row['order_id'];
        $data['purchase_date'] = $row['purchase_date'];
        $data['fullname'] = $row['fullname'];
        $data['orders'] = [];

        // เพิ่มรายละเอียดการสั่งซื้อลอตเตอรี่เป็น nested array
        $getOrderDetailsQuery = "SELECT
                                    l.lottery_number,
                                    d.quantity_order,
                                    l.price,
                                    (d.quantity_order * l.price) AS total_price
                                FROM
                                    order_details d
                                INNER JOIN
                                    lottery l ON d.lottery_idx = l.idx
                                WHERE
                                    d.order_id = ?";

        $stmt = $conn->prepare($getOrderDetailsQuery);
        $stmt->bind_param("i", $data['order_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $orderData = [
                'lottery_number' => (int) $row['lottery_number'],
                'quantity_order' => $row['quantity_order'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
            ];
            $data['orders'][] = $orderData;
        }

        // คำนวณราคารวมทั้งหมด
        $totalOrderPrice = array_reduce($data['orders'], function ($carry, $item) {
            return $carry + $item['total_price'];
        }, 0);
        $data['total_order_price'] = $totalOrderPrice;

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        // ไม่พบข้อมูล
        $responseJson = [
            'status' => 'error',
            'message' => 'No orders found for this user.',
        ];

        $response->getBody()->write(json_encode($responseJson));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});


// รับข้อมูล order
$app->post('/lottery/order', function (Request $request, Response $response, $args) {
    // รับข้อมูลการสั่งซื้อจากคำขอ
    $data = $request->getBody();
    $data = json_decode($data, true);

    // เช็คข้อมูลที่ต้องการจากข้อมูลที่รับเข้ามา
    $userId = $data['user_id'];
    $orderDate = date('Y-m-d H:i:s');
    $lotteryDetails = $data['lottery_details'];

    // เพิ่มข้อมูลการสั่งซื้อลอตเตอรี่ในตาราง lottery_order
    $conn = $GLOBALS['connect'];
    $insertOrderQuery = "INSERT INTO lottery_order (user_id, purchase_date) VALUES (?, ?)";
    $stmt = $conn->prepare($insertOrderQuery);
    $stmt->bind_param("ss", $userId, $orderDate);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // เพิ่มรายละเอียดการสั่งซื้อลอตเตอรี่ในตาราง order_details
    $insertDetailsQuery = "INSERT INTO order_details (order_id, lottery_idx, quantity_order) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insertDetailsQuery);

    foreach ($lotteryDetails as $detail) {
        $stmt->bind_param("iii", $orderId, $detail['lottery_idx'], $detail['quantity_order']);
        $stmt->execute();
    }

    $stmt->close();

    // สร้าง JSON สำหรับการตอบกลับ
    $responseJson = [
        'status' => 'success',
        'message' => 'Order placed successfully',
        'order_id' => $orderId,
    ];

    $response->getBody()->write(json_encode($responseJson));
    return $response->withHeader('Content-Type', 'application/json');
});







/// hstory



//

$app->get('/lotteries/admin/report_order', function (Request $request, Response $response) {
    // ค้นหาข้อมูลประวัติการสั่งซื้อทั้งหมดของผู้ใช้ทั้งหมด
    $queryParams = $request->getQueryParams();
    $dateFilter = '';

    // ตรวจสอบว่ามีพารามิเตอร์ 'daily' หรือ 'monthly' ในคำขอ
    if (isset($queryParams['daily'])) {
        // ถ้ามีพารามิเตอร์ 'daily' ให้รับวันที่และแปลงให้อยู่ในรูปแบบของ MySQL DATE
        $dailyDate = $queryParams['daily'];
        $dailyDate = DateTime::createFromFormat('d/m/Y', $dailyDate)->format('Y-m-d');
        $dateFilter = "WHERE DATE(o.purchase_date) = '$dailyDate'";
    } elseif (isset($queryParams['monthly'])) {
        // ถ้ามีพารามิเตอร์ 'monthly' ให้รับเดือนและปีและแปลงให้อยู่ในรูปแบบของ MySQL DATE
        $monthlyDate = $queryParams['monthly'];
        $monthlyDate = DateTime::createFromFormat('m/Y', $monthlyDate)->format('Y-m-d');
        $dateFilter = "WHERE MONTH(o.purchase_date) = MONTH('$monthlyDate') AND YEAR(o.purchase_date) = YEAR('$monthlyDate')";
    }// เรียกใช้งานค่า report_type จากคิวรีสตริง


    $getHistoryQuery = "SELECT o.order_id, o.purchase_date, m.fullname,m.user_id,
                                l.lottery_number, d.quantity_order, l.price, (d.quantity_order * l.price) AS total_price
                        FROM lottery_order o
                        INNER JOIN member m ON o.user_id = m.user_id
                        INNER JOIN order_details d ON o.order_id = d.order_id
                        INNER JOIN lottery l ON d.lottery_idx = l.idx
                        $dateFilter
                        ORDER BY o.order_id ASC";
    $conn = $GLOBALS['connect'];
    $result = $conn->query($getHistoryQuery);

    if ($result->num_rows > 0) {
        $data = [];

        while ($row = $result->fetch_assoc()) {

            $orderData = [

                'lottery_number' => (int) $row['lottery_number'],
                // แปลงเป็น int(6)
                'quantity_order' => $row['quantity_order'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
            ];

            // เรียงข้อมูลใน $data ตาม order_id
            // ใช้ $row['order_id'] ในการสร้างดัชนีของอาร์เรย์ $data
            $data[$row['order_id']]['purchase_date'] = $row['purchase_date'];
            $data[$row['order_id']]['order_id'] = $row['order_id'];
            $data[$row['order_id']]['user_id'] = $row['user_id'];
            $data[$row['order_id']]['fullname'] = $row['fullname'];
            $data[$row['order_id']]['orders'][] = $orderData;

        }

        // คำนวณราคารวมทั้งหมด
        foreach ($data as &$order) {
            $order['total_order_price'] = array_reduce($order['orders'], function ($carry, $item) {
                return $carry + $item['total_price'];
            }, 0);
        }

        $response->getBody()->write(json_encode(array_values($data)));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        // ไม่พบข้อมูล
        $responseJson = [
            'status' => 'error',
            'message' => 'No orders found.',
        ];

        $response->getBody()->write(json_encode($responseJson));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});



$app->get('/member/history_orders/{user_id}', function (Request $request, Response $response, $args) {
    // รับค่า user_id จาก URL
    $user_id = $args['user_id'];
    
    // คำสั่ง SQL เพื่อค้นหาประวัติการสั่งซื้อของผู้ใช้ที่ระบุ
    $getHistoryQuery = "SELECT o.order_id, o.purchase_date, m.fullname,m.user_id,
                        l.lottery_number, d.quantity_order, l.price, (d.quantity_order * l.price) AS total_price
                        FROM lottery_order o
                        INNER JOIN member m ON o.user_id = m.user_id
                        INNER JOIN order_details d ON o.order_id = d.order_id
                        INNER JOIN lottery l ON d.lottery_idx = l.idx
                        WHERE o.user_id = ?
                        ORDER BY o.order_id, l.lottery_number";

    // เชื่อมต่อกับฐานข้อมูล
    $conn = $GLOBALS['connect'];

    // เตรียมคำสั่ง SQL และผูกพารามิเตอร์
    $stmt = $conn->prepare($getHistoryQuery);
    $stmt->bind_param("s", $user_id);

    // รันคำสั่ง SQL
    $stmt->execute();

    // รับผลลัพธ์
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $orderData = [
                'lottery_number' => (int)$row['lottery_number'],
                'quantity_order' => $row['quantity_order'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
            ];

            // ตรวจสอบว่า order_id เปลี่ยนแปลงหรือไม่
            if (!isset($data[$row['order_id']])) {
                $data[$row['order_id']] = [
                    'purchase_date' => $row['purchase_date'],
                    'order_id' => $row['order_id'],
                    'fullname' => $row['fullname'],
                    'orders' => [],
                ];
            }

            // เพิ่มข้อมูลการสั่งซื้อใน order นั้น
            $data[$row['order_id']]['orders'][] = $orderData;
            foreach ($data as &$order) {
                $order['total_order_price'] = array_reduce($order['orders'], function ($carry, $item) {
                    return $carry + $item['total_price'];
                }, 0);
            }
         }

        // แปลงผลลัพธ์เป็นอาร์เรย์และส่งให้กับ response
        $response->getBody()->write(json_encode(array_values($data)));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        // ไม่พบข้อมูล
        $responseJson = [
            'status' => 'error',
            'message' => 'No orders found for the specified user.',
        ];

        $response->getBody()->write(json_encode($responseJson));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    
});

$app->get('/history/{user_id}', function (Request $request, Response $response, $args) {
    // รับค่า user_id จาก URL
    $user_id = $args['user_id'];
    
    // คำสั่ง SQL เพื่อค้นหาประวัติการสั่งซื้อของผู้ใช้ที่ระบุ
    $getHistoryQuery = "SELECT o.purchase_date, m.fullname, l.lottery_number, d.quantity_order, l.price, (d.quantity_order * l.price) AS total_price
                        FROM lottery_order o
                        INNER JOIN member m ON o.user_id = m.user_id
                        INNER JOIN order_details d ON o.order_id = d.order_id
                        INNER JOIN lottery l ON d.lottery_idx = l.idx
                        WHERE o.user_id = ?
                        ORDER BY o.purchase_date DESC";

    // เชื่อมต่อกับฐานข้อมูล
    $conn = $GLOBALS['connect'];

    // เตรียมคำสั่ง SQL และผูกพารามิเตอร์
    $stmt = $conn->prepare($getHistoryQuery);
    $stmt->bind_param("s", $user_id);

    // รันคำสั่ง SQL
    $stmt->execute();

    // รับผลลัพธ์
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $orderData = [
                'purchase_date' => $row['purchase_date'],
                'fullname' => $row['fullname'],
                'lottery_number' => (int)$row['lottery_number'],
                'quantity_order' => $row['quantity_order'],
                'price' => $row['price'],
                'total_price' => $row['total_price'],
            ];

            $data[] = $orderData;
        }

        // แปลงผลลัพธ์เป็น JSON และส่งให้กับ response
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        // ไม่พบข้อมูล
        $responseJson = [
            'status' => 'error',
            'message' => 'No orders found for the specified user.',
        ];

        $response->getBody()->write(json_encode($responseJson));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});
