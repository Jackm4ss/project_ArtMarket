<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Deleted = 'deleted';
}
