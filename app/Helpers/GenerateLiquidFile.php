<?php

namespace App\Helpers;

class GenerateLiquidFile {

    private object $shopDetails;
    private array $variables = [];
    private string $file = '';
    private $shopSelectors;

    public function __construct(object $shopDetails) {
        $this->shopDetails = $shopDetails;
    }

    /**
     * @throws \Exception
     */
    public function generate($autoInit = true) : string {

        if (!$autoInit) {

            if (empty($this->variables) || empty($this->file)) {
                throw new \Exception('When you using custom generate, you need to specify the variables and the file.');
            }

        }

        // Get Variables
        $this->getVariables();

        // Get File
        $this->getFile();

        // Get Shop Selectors
        $this->getShopSelectors();

        // Replace variables with values;
        $this->prepareFile();

        return $this->file;
    }

    /**
     * Replace the variables in file
     */
    private function prepareFile() : void {

        $shopSelectors = $this->shopSelectors->toArray();

        foreach ($this->variables as $key => $placeholder) {

            if ($placeholder == 'ra_key') {
                $this->file = str_replace('{{ra_key}}', $this->shopDetails->tracking_key, $this->file);
                continue;
            }

            if ($placeholder == 'app_url') {
                $this->file = str_replace('{{app_url}}', env('APP_URL'), $this->file);
                continue;
            }

            $this->file = str_replace('{{'.$placeholder.'}}', $shopSelectors[$placeholder], $this->file);
        }

    }

    /**
     * @param string $path
     * @return void
     *
     * Get variables from config
     */
    public function getVariables($path = 'shopify-variables') : void {
        $this->variables = config($path);
    }


    /**
     * @param string $path
     * @return void
     *
     * Get file from Storage
     */
    public function getFile($path = '_ra.liquid') : void {
        $this->file = \Illuminate\Support\Facades\Storage::get($path);
    }

    /**
     * @return mixed
     *
     * Get / Create shopSelectors
     */
    private function getShopSelectors() {

        if ($this->shopDetails->shopSelectors === null) {
            $this->shopDetails->shopSelectors()->create([]);
        }

        $this->shopSelectors = $this->shopDetails->shopSelectors ?? $this->shopDetails->shopSelectors()->first();

    }

}
