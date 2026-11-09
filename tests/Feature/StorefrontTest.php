<?php
namespace Tests\Feature;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class StorefrontTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void { parent::setUp(); $this->seed(); }
    public function test_homepage_shows_seeded_products(): void
    {
        $this->get('/')->assertOk()->assertSee('Vermontshop')->assertSee('To‘qimali trikotaj sviter')->assertDontSee('Textured knit sweater');
    }
    public function test_gender_filter_shows_only_matching_products(): void
    {
        $this->get('/?gender=men')->assertOk()->assertSee('Erkin bichimli paxta ko‘ylak')->assertDontSee('To‘qimali trikotaj sviter');
    }

    public function test_catalog_pages_are_separate_and_product_content_follows_selected_locale(): void
    {
        $this->get('/ayollar')->assertOk()->assertSee('Kundalik charm sumka')->assertDontSee('Erkin bichimli paxta ko‘ylak');
        $this->get('/aksessuarlar')->assertOk()->assertSee('Minimal charm hamyon')->assertDontSee('To‘qimali trikotaj sviter');

        $this->get('/til/en')->assertRedirect();
        $this->get('/')->assertOk()->assertSee('Textured knit sweater')->assertDontSee('To‘qimali trikotaj sviter');
    }
    public function test_adding_product_to_cart_stores_its_id_and_quantity_in_session(): void
    {
        $product=Product::firstOrFail();
        $this->post('/savat',['product_id'=>$product->id,'quantity'=>2,'variant'=>'Black / M'])
            ->assertRedirect()->assertSessionHas("cart.{$product->id}.quantity",2);
    }
}
