<?php

class shopGiftforyouPluginFrontendSendAction extends waViewAction
{
    public function execute()
    {
        // Устанавливаем Content-type для JSON
        $this->getResponse()->addHeader("Content-type", "application/json");
        
        if (!waRequest::isXMLHttpRequest()) {
            echo json_encode(["success" => false, "error" => "Only AJAX requests allowed"]);
            exit;
        }

        $email = waRequest::post("email", "", "string");
        $product_id = waRequest::post("product_id", 0, "int");

        if (!$email || !$product_id) {
            echo json_encode(["success" => false, "error" => "Не все поля заполнены"]);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "error" => "Некорректный email"]);
            exit;
        }

        $product = new shopProduct($product_id);
        if (!$product->getId()) {
            echo json_encode(["success" => false, "error" => "Товар не найден"]);
            exit;
        }

        // Логируем в файл
        $log_result = $this->logEmail($email, $product);
        
        if ($log_result) {
            echo json_encode(["success" => true, "message" => "Письмо отправлено! Информация сохранена."]);
        } else {
            echo json_encode(["success" => false, "error" => "Ошибка сохранения информации."]);
        }
        
        // ОБЯЗАТЕЛЬНО выходим чтобы не рендерить шаблон
        exit;
    }

    private function logEmail($email, $product)
    {
        try {
            $product_url = wa()->getRouteUrl("shop/frontend/product", [
                "product_url" => $product["url"]
            ], true);

            $email_content = "=" . str_repeat("=", 60) . "\n";
            $email_content .= "📧 ИМИТАЦИЯ ОТПРАВКИ ПИСЬМА\n";
            $email_content .= "⏰ ВРЕМЯ: " . date("Y-m-d H:i:s") . "\n";
            $email_content .= "📨 ПОЛУЧАТЕЛЬ: " . $email . "\n";
            $email_content .= "=" . str_repeat("=", 60) . "\n\n";
            
            $email_content .= "Тема: Ваш подарок!\n\n";
            $email_content .= "Здравствуйте!\n\n";
            $email_content .= "Ваш подарок — товар: \"{$product["name"]}\"\n\n";
            $email_content .= "Цена: {$product["price"]}\n";
            $email_content .= "Ссылка: {$product_url}\n\n";
            $email_content .= "Спасибо, что участвуете в акции!\n\n";
            $email_content .= "=" . str_repeat("=", 60) . "\n\n";

            $email_log_file = "/var/www/html/wa-apps/shop/plugins/giftforyou/logs/sent_emails.log";
            $log_dir = dirname($email_log_file);

            if (!file_exists($log_dir)) {
                mkdir($log_dir, 0777, true);
            }

            $result = file_put_contents($email_log_file, $email_content, FILE_APPEND | LOCK_EX);
            
            return $result !== false;

        } catch (Exception $e) {
            error_log("Email log error: " . $e->getMessage());
            return false;
        }
    }
}
