<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/members', function (Request $request, Response $response, $args) {
    $conn = $GLOBALS['connect'];
    $sql = 'select * from member';
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        unset($row['password']);
        array_push($data, $row);
    }

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withStatus(200);
});
$app->get('/member/{email}', function(Request $request, Response $response, $args){
    $conn = $GLOBALS['connect'];
    $email = $args['email'];
    $sql = 'select * from member where email = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array();
    foreach ($result as $row) {
        unset($row['password']); // ไม่ส่งรหัสผ่านกลับ
        array_push($data, $row);
    }
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

/////
$app->post('/member/login', function (Request $request, Response $response) {
    $jsonbody = $request->getBody();
    $data = json_decode($jsonbody,true);
    $email = $data['email'];
    $password = $data['password'];
    $conn = $GLOBALS['connect'];
    // เข้ารหัสรหัสผ่านที่ผู้ใช้ป้อน
 
    // ตรวจสอบรหัสผ่านในฐานข้อมูล
    
    // (ใช้ $conn แทน $db)
    $query = "SELECT * FROM member WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $storedHashedPassword = $row['password'];

        // ตรวจสอบรหัสผ่านที่เข้ารหัสแล้วกับรหัสผ่านที่เก็บในฐานข้อมูล
        if (password_verify($password, $storedHashedPassword)) {
            // รหัสผ่านถูกต้อง
            unset($row['password']); // ไม่ส่งรหัสผ่านกลับ
            $response->getBody()->write(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
          
        } else {
            // รหัสผ่านไม่ถูกต้อง
            $response->getBody()->write(json_encode(["message" => "รหัสผ่านไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    } else {
        // ไม่พบผู้ใช้งาน
        $response->getBody()->write(json_encode(["message" => "ไม่พบสมาชิก"], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        
    }
});

$app->post('/member/register', function (Request $request, Response $response,$args) {
    $jsonbody = $request->getBody();
    $json_data = json_decode($jsonbody,true);
    $email = $json_data['email'];
    $password = $json_data['password'];
    $fullname = $json_data['fullname'];
    $birthdate = $json_data['birthdate'];
    $phoneNo = $json_data['phone_no'];
     
    // เข้ารหัสรหัสผ่าน
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
   
  
    // เพิ่มข้อมูลผู้ใช้ใหม่ลงในฐานข้อมูล
    $conn = $GLOBALS['connect'];
    $sql = 'insert into member ( email, password, fullname, birthdate, phone_no, roles) values (?, ?, ?, ?, ?, \'member\')';    
    $insertStmt = $conn->prepare($sql);
    $insertStmt->bind_param('sssss',$email, $hashedPassword, $fullname, $birthdate, $phoneNo);
    $insertStmt->execute();
    $affected=$insertStmt->affected_rows;
    if ($affected > 0) {

        $data = ["affected_rows" => $affected, "last_idx" => $conn->insert_id];
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
    

    //
    
});

// $app->post('/member/orders',function(Request $request, Response $response,$args){
//     $jsonbody = $request->getBody();
//     $json_data = json_decode($jsonbody,true);

//     $conn = $GLOBALS['connect'];
// });

  // ตรวจสอบบทบาท (roles) ของผู้ใช้
            /*  
             $payload = [
                "user_id" => $row['user_id'],
                "email" => $row['email'],
                "roles" => $row['roles'] // ให้บทบาทมาจากฐานข้อมูล
            ];
           $config = require("config.php");

            // กำหนด secret key ของคุณ
            $secretKey = $config['secret_key'];
            // สร้าง JWT
            $token = JWT::encode($payload, $secretKey, 'HS256');

            // สร้าง JSON response รวม role
            $responsePayload = [
                "token" => $token,
                "role" => $payload["roles"]
            ];

            $response->getBody()->write(json_encode($responsePayload, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
            */