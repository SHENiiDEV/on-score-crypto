<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_docs_page_renders_successfully(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('On-Score');
        $response->assertSee('API Docs');
        $response->assertSee('openapi.yaml');
    }

    public function test_openapi_yaml_spec_file_is_accessible(): void
    {
        $response = $this->get('/openapi.yaml');

        $response->assertStatus(200);
        $response->assertSee('openapi: 3.0.3');
        $response->assertSee('On-Score B2B Intelligence', false);
    }

    public function test_landing_page_renders_with_payadmit_style_and_simulator(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('On-Score');
        $response->assertSee('SUPER_SHARK');
        $response->assertSee('How On-Score Analyzes Players');
        $response->assertSee('5 Chains');
        $response->assertSee('Developer Integration');
    }
}


