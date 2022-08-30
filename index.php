<?php 
 declare(strict_types=1);

 header("content-type: application/json; charset: utf-8");
 header("Access-Control-Allow-Origin: http://localhost:3000");
 
 spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
 });


 // error handlers
 set_error_handler("ErrorHandler::handleError");
 set_exception_handler("ErrorHandler::handleException");
 

 
  $parts = explode("/", $_SERVER["REQUEST_URI"]);
  // print_r($parts);
  if ($parts[2] != "products") {
    http_response_code(404);
    echo json_encode([
      "message" => "not found",
      "error" => true,
      "success" => false,
    ]);
    exit;
  }


   $id = $parts[3] ?? null;

   // connecting database
   $database = new Database("localhost", "rest_api_php", "root", "07086314122");
  //  $database->getConnection();

  $gateWay = new ProductGateway($database);

  $controller = new ProductController($gateWay);
  $controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
?>