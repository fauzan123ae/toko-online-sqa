<?php

use PHPUnit\Framework\TestCase;
use App\Checkout;

require_once __DIR__ . '/../src/Checkout.php';

class CheckoutTest extends TestCase
{
    private $checkout;
    private $produkFile;
    private $pesananFile;

    protected function setUp(): void
    {
        $this->produkFile = __DIR__ . '/test_products.json';
        $this->pesananFile = __DIR__ . '/test_orders.json';

        // Data produk dummy
        $products = [
            "PRD-001" => [
                "nama" => "Kemeja Flanel",
                "harga" => 150000,
                "stok" => 10
            ],
            "PRD-002" => [
                "nama" => "Celana Jeans",
                "harga" => 250000,
                "stok" => 5
            ]
        ];

        file_put_contents($this->produkFile, json_encode($products, JSON_PRETTY_PRINT));
        file_put_contents($this->pesananFile, json_encode([], JSON_PRETTY_PRINT));

        $this->checkout = new Checkout($this->produkFile, $this->pesananFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->produkFile);
        @unlink($this->pesananFile);
    }

    // Path 1
    public function testCheckoutNormalTanpaDiskon()
    {
        $keranjang = [
            "PRD-001" => 1
        ];

        $result = $this->checkout->prosesCheckout("test@mail.com", "Madiun", $keranjang);

        $this->assertEquals(170000, $result['total_bayar']);
    }

    // Path 2
    public function testCheckoutGratisOngkir()
    {
        $keranjang = [
            "PRD-001" => 2,
            "PRD-002" => 1
        ];

        $result = $this->checkout->prosesCheckout("test@mail.com", "Madiun", $keranjang);

        $this->assertEquals(550000, $result['total_bayar']);
    }

    // Path 3
    public function testCheckoutDiskon10Persen()
    {
        $keranjang = [
            "PRD-002" => 5
        ];

        $result = $this->checkout->prosesCheckout("test@mail.com", "Madiun", $keranjang);

        $this->assertEquals(1125000, $result['total_bayar']);
    }

    public function testCheckoutKeranjangKosong()
    {
        $this->expectException(Exception::class);

        $this->checkout->prosesCheckout("test@mail.com", "Madiun", []);
    }

    public function testCheckoutAlamatKosong()
    {
        $this->expectException(Exception::class);

        $this->checkout->prosesCheckout("test@mail.com", "", [
            "PRD-001" => 1
        ]);
    }

    public function testQtyTidakValid()
    {
        $this->expectException(Exception::class);

        $this->checkout->prosesCheckout("test@mail.com", "Madiun", [
            "PRD-001" => 0
        ]);
    }

    public function testProdukTidakValid()
    {
        $this->expectException(Exception::class);

        $this->checkout->prosesCheckout("test@mail.com", "Madiun", [
            "INVALID" => 1
        ]);
    }

    public function testStokTidakCukup()
    {
        $this->expectException(Exception::class);

        $this->checkout->prosesCheckout("test@mail.com", "Madiun", [
            "PRD-002" => 10
        ]);
    }
}