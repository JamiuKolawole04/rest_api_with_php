<?php 
  class ProductController {
    public function __construct(private ProductGateway $gateway) {

    }
    
    public function processRequest(string $method, ?string $id) : void {
        // var_dump($method, $id);
        if ($id) {
             $this->processResourceRequest($method, $id);
        } else {
           $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest( string $method, string $id) : void {
         $product = $this->gateway->get($id);

         if (!$product) {
          http_response_code(404);

          echo json_encode([
            "success" => false,
            "statusCode" => http_response_code(),
            "product" => "not found",
          ]);

          return;
         }

         switch($method) {
          case "GET": 
              echo json_encode([
              "success" => true,
              "statusCode" => http_response_code(),
              "product" => $product,
           ]);

          break;

          case "PATCH":
              $data = (array) json_decode(file_get_contents("php://input"), true);

              $errors = $this->getValidtionErrors($data, false);

              if (!empty($errors)) {
                http_response_code(400);
                  
               echo json_encode(["errors" => $errors]);
               break;
             }

            // var_dump($data);
              $rows = $this->gateway->update($product, $data);

              // http_response_code(201);

              echo json_encode([
               "message" => "Product $id updated",
               "rows" => $rows,
             ]);

            break;

          // case "OPTIONS":
          // header("Access-Control-Allow-Origin: http://localhost:3000");
          //   $rows = $this->gateway->delete($id);                
          //   echo json_encode([
          //     "message" => "Product $id deleted",
          //     "rows" => $rows,
          //   ]);
          //  break; 

        case "DELETE":
          // header("Access-Control-Allow-Origin: http://localhost:3000");
            $rows = $this->gateway->delete($id);                
            echo json_encode([
              "message" => "Product $id deleted",
              "rows" => $rows,
            ]);
           break; 


        default: 
            http_response_code(405);
            header('Allow: GET, POST, PATCH, DELETE, OPTIONS');
            echo json_encode([
            "message" => "not allowed",
            "error" => true,
            "success" => false
            ]);   

          }


        
    }

    private function processCollectionRequest(string $method ) : void {
        switch ($method) {
          case"GET": 
            echo json_encode([
              "success" => true,
              "statusCode" => http_response_code(),
              "products" => $this->gateway->getAll(),
            ]);
            break;

          case "POST": 
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidtionErrors($data);

            if (!empty($errors)) {
              http_response_code(400);
              
              echo json_encode(["errors" => $errors]);
              break;
            }

            // var_dump($data);
            $id = $this->gateway->create($data);

            http_response_code(201);

            echo json_encode([
              "message" => "Product created",
              "id" => $id
            ]);

            break;


          default: 
            http_response_code(405);
            header('Allow: GET, POST, PATCH, DELETE, OPTIONS');
            echo json_encode([
            "message" => "not allowed",
            "error" => true,
            "success" => false
            ]);
        }
    }

    private function getValidtionErrors(array $data, bool $is_new = true) : array {
      $errors = [];
      if ($is_new && empty($data["name"]) || $is_new && empty($data["description"])) {
        $errors[] = "name and description are required";
      }

      if (array_key_exists("size", $data)) {
        if (filter_var($data["size"], FILTER_VALIDATE_INT) === false) {
          $errors[] = "size must be an integer";
        }
      }

      return $errors;
    }


   
  }

  
?>