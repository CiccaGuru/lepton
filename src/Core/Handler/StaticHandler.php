<?php

namespace Lepton\Core\Handler;
use Lepton\Routing\Match\{Match404, MatchFile};
use Lepton\Http\Response\FileResponse;
use Lepton\Core\Application;
use Lepton\Http\Response\NotFoundResponse;

class StaticHandler extends AbstractHandler
{
    public function resolveRequest() : MatchFile|Match404{

        // Get the requested file path
        $url = preg_replace("/^\/".Application::getAppConfig()->base_url."\//", "/", $this->request->url);
        $filePath = Application::$documentRoot. Application::getAppConfig()->static_files_dir . $url;

        // Check if the file exists
        if (file_exists($filePath)) {
            // Get the file extension
            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            // Set content type based on the file extension
            switch ($fileExtension) {
                case 'png':
                    $contentType = 'image/png';
                    break;
                case 'jpg':
                case 'jpeg':
                    $contentType = 'image/jpeg';
                    break;
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'css':
                    $contentType = 'text/css';
                    break;
                case 'js':
                    $contentType = 'text/javascript';
                    break;
                case 'pdf':
                    $contentType = 'application/pdf';
                    break;
                default:
                    $contentType = '';
                    break;
            }
            return new MatchFile(filePath: $filePath, contentType: $contentType);
        } else {
          return new Match404();
        }
    }

    public function handle($match): FileResponse|NotFoundResponse{
      if($match instanceof Match404){
        return new NotFoundResponse();
      }
      return new FileResponse($match->filePath, $match->contentType);
    }
}
