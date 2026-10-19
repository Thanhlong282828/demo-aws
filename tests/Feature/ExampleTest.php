<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_redirects_to_product_list(): void
    {
        $this->get('/')->assertRedirect(route('products.index'));
    }
}
