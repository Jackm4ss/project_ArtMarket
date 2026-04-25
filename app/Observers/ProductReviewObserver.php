<?php

namespace App\Observers;

use App\Models\ProductReview;
use App\Services\Reviews\ProductReviewService;

class ProductReviewObserver
{
    public function saved(ProductReview $productReview): void
    {
        $this->refresh($productReview);
    }

    public function deleted(ProductReview $productReview): void
    {
        $this->refresh($productReview);
    }

    public function restored(ProductReview $productReview): void
    {
        $this->refresh($productReview);
    }

    public function forceDeleted(ProductReview $productReview): void
    {
        $this->refresh($productReview);
    }

    private function refresh(ProductReview $productReview): void
    {
        app(ProductReviewService::class)->refreshAggregates($productReview->product_id);
    }
}
