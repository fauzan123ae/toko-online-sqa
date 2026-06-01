<?php

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;

class SystemTest extends TestCase
{
    private $driver;
    private $baseUrl = 'http://localhost:8000';

    protected function setUp(): void
    {
        // Mengarahkan ke ChromeDriver lokal
        $host = 'http://localhost:9515/wd/hub';

        $chromeOptions = new ChromeOptions();

        // Mode headless untuk Linux / GitHub Actions
        $chromeOptions->addArguments([
            '--headless',
            '--disable-gpu',
            '--no-sandbox'
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(
            ChromeOptions::CAPABILITY,
            $chromeOptions
        );

        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    public function testHomepageAndSearchFeature()
    {
        // Kunjungi website lokal
        $this->driver->get($this->baseUrl);

        // Validasi halaman memuat teks utama
        $bodyText = $this->driver
            ->findElement(WebDriverBy::tagName('body'))
            ->getText();

        $this->assertStringContainsString(
            'Toko Online',
            $bodyText
        );

        // Cari produk
        $searchBox = $this->driver
            ->findElement(WebDriverBy::name('cari'));

        $searchBox->sendKeys('Kemeja');

        // Submit form pencarian
        $searchBox->submit();

        // Tunggu halaman reload
        sleep(2);

        // Ambil ulang isi halaman
        $updatedBodyText = $this->driver
            ->findElement(WebDriverBy::tagName('body'))
            ->getText();

        // Validasi hasil pencarian muncul
        $this->assertStringContainsString(
            'Kemeja Flanel',
            $updatedBodyText
        );
    }

    protected function tearDown(): void
    {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}
#