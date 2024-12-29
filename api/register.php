<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/quickservemobile.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->name) &&
    !empty($data->UBmail) &&
    !empty($data->password) &&
    !empty($data->department)
) {
    // Check if email already exists
    $check_query = "SELECT id FROM registration WHERE UBmail = :UBmail";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":UBmail", $data->email);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(array("message" => "Email already exists."));
        exit();
    }

    // Insert the new user
    $query = "INSERT INTO registration (name, UBmail, password,department) VALUES (:name, :UBmail, :password, :department)";
    $stmt = $db->prepare($query);

    $name = htmlspecialchars(strip_tags($data->name));
    $UBmail = htmlspecialchars(strip_tags($data->UBmail));
    $password = password_hash($data->password, PASSWORD_DEFAULT);
    $department = htmlspecialchars($data->department);

    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":UBmail", $UBmail);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":department", $department);

    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(array(
            "success" => true,
            "message" => "User registered successfully."
        ));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to register user."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register user. Data is incomplete."));
}
?>