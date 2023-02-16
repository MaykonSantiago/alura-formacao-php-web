<?php

namespace Alura\Mvc\Controller;

use Alura\Mvc\Entity\Video;
use Alura\Mvc\Helper\FlashMessageTrait;
use Alura\Mvc\Repository\VideoRepository;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NewVideoController implements Controller
{
    use FlashMessageTrait;

    public function __construct(private VideoRepository $repository)
    {
    }

    public function processarRequisicao(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $url = filter_var($body['url'], FILTER_VALIDATE_URL);
        if ($url === false) {
            $this->addErrorMessage('URL inválida');
            return new Response(302, [
                'Location' => '/novo-video'
            ]);
        }
        $titulo = filter_var($body['titulo']);
        if ($titulo === false) {
            $this->addErrorMessage('Título não informado');
            return new Response(302, [
                'Location' => '/novo-video'
            ]);
        }

        $video = new Video($url, $titulo);

        if($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fInfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fInfo->file($_FILES['image']['tmp_name']);

            if (str_starts_with($mimeType, 'image/')) {
                $safeFileName = uniqid('upload_') . '_' . pathinfo($_FILES['image']['name'], PATHINFO_BASENAME);
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    __DIR__ . '/../../public/img/uploads/' . $_FILES['image']['name']
                );
                $video->setImagePath($safeFileName);
            }
        }
        
        $success = $this->repository->add($video);
        if ($success === false) {
            $this->addErrorMessage('Erro ao cadastrar vídeo');
            return new Response(302, [
                'Location' => '/novo-video'
            ]);
        } else {
            return new Response(302, [
                'Location' => '/?sucesso=1'
            ]);
        }
    }
}
