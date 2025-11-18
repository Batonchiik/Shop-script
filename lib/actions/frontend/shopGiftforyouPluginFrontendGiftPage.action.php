<?php

class shopGiftforyouPluginFrontendGiftPageAction extends waViewAction
{
    public function execute()
    {
        $plugin = wa()->getPlugin('giftforyou');
        $settings = $plugin->getSettings();
        $product_ids = $settings['product_ids'] ?? '';
        $ids = array_filter(array_map('intval', explode(',', $product_ids)));

        if (empty($ids)) {
            $this->view->assign('error', 'Нет товаров для выбора подарка');
            return;
        }

        // Выбираем случайный товар
        $random_key = array_rand($ids);
        $product_id = $ids[$random_key];
        $product = new shopProduct($product_id);

        if (!$product->getId()) {
            $this->view->assign('error', 'Товар не найден');
            return;
        }

        $product_url = wa()->getRouteUrl('shop/frontend/product', [
            'product_url' => $product['url']
        ], true);

        $this->view->assign([
            'product' => $product,
            'product_url' => $product_url,
        ]);
    }
}