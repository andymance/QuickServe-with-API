<?php
header("Access-Control-Allow-Origin: *"); // Allow all origins
header("Content-Type: application/json; charset=UTF-8"); // Set content type to JSON
header("Access-Control-Allow-Methods: POST"); // Allow POST method
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); // Allow headers

include_once '../config/quickservemobile.php'; // Include database configuration

$database = new Database(); // Create a new database object
$db = $database->getConnection(); // Get the database connection

$data = json_decode(file_get_contents("php://input")); // Get JSON data from input

if (!empty($data->UBmail) && !empty($data->password)) {
    $query = "SELECT id, name, UBmail, password, department FROM registration WHERE UBmail = :UBmail";
    $stmt = $db->prepare($query);

    $UBmail = htmlspecialchars(strip_tags($data->UBmail));
    $stmt->bindParam(":UBmail", $UBmail);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row['id'];
        $name = $row['name'];
        $UBmail = $row['UBmail'];
        $password = $row['password'];
        $department = $row['department'];

        if (password_verify($data->password, $password)) {
            http_response_code(200);
            echo json_encode(array(
                "success" => true,
                "message" => "Login successful.",
                "registration" => array(
                    "id" => $id,
                    "name" => $name,
                    "UBmail" => $UBmail,
                    "department" => $department
                )
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid credentials."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Invalid credentials."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to login. Data is incomplete."));
}
?>