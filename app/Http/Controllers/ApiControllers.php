<?php
// Write by Bengsky
namespace App\Http\Controllers;

use GuzzleHttp;
use Rawilk\Printing\Facades\Printing;

class ApiControllers extends Controller
{
    public $printerList = [];

    public function __construct()
    {
        $printers = Printing::printers();
        foreach ($printers as $list) {
            if ($list->status() == "idle" || $list->status() == "online")
                $this->printerList[] = $list->id();
        }
        $printers = Printing::printer(getenv("PRINTER_ID"));


    }
    private function getStatus($path)
    {
        $api_url = env("API_URL");
        $api_key = env("API_KEY");
        $client = new \GuzzleHttp\Client();
        $curl = $client->request(
            'GET',
            $api_url . $path,
            [
                "headers" => [
                    "content-type" => "application/json",
                    "api_key" => $api_key,
                ]
            ],
        );
        $status = $curl->getBody()->getContents();
        return $status;
    }
    private function doPrint($print_type, $id)
    {
        $doc = getenv("BASE_URL") . "/uploads/$id/$id.pdf";
        if (getenv("PRINTING_DRIVER") == "printnode") {
            $printer = getenv("PRINTER_ID");
        } else {
            $printer = $print_type == 0 ? "BNW" : "COLOR";
        }
        if ($print_type == 0) {
            file_put_contents('input.pdf', file_get_contents($doc));
            exec("gs -sDEVICE=pdfwrite -sProcessColorModel=DeviceGray -sColorConversionStrategy=Gray -dOverrideICC -o file.pdf -f input.pdf");
        } else {
            file_put_contents('file.pdf', file_get_contents($doc));
        }
        Printing::newPrintTask()
            ->printer($printer)->file('file.pdf')->send();
    }
    public function init($id)
    {
        $page = $this->getStatus("/api/" . $id . "/init");
        if (count($this->printerList) == 0) {
            die('{"success":false, "msg":404}');
        }
        echo $page;
    }
    public function status($id)
    {

        $status = $this->getStatus("/api/" . $id . "/status");
        $jStatus = json_decode($status);
        if ($jStatus->status == 4) {
            $this->doPrint($jStatus->print_type, $id);

            if (getenv("PRINTING_DRIVER") == "printnode") {
                // $this->getStatus("/api/" . $id . "/printnode");
                $this->getStatus("/api/" . $id . "/destroy");
            } else {

                label:
                $a = exec("lpstat -W not-completed");
                if ($a == "") {
                    $this->getStatus("/api/" . $id . "/destroy");
                } else {
                    sleep(5);
                    goto label;
                }
            }

        }
        return $status;
    }
}