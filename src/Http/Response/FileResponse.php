<?php

namespace Lepton\Http\Response;

 class FileResponse extends SuccessResponse
{

    public function __construct(
        public $filePath,
        $contentType,

        )
    {
        parent::__construct(headers: ["Content-Type" => $contentType]);
    }

    public function sendBody(){
        readfile($this->filePath);
    }


}
