<?php
/**
 * User: Andreas Warnaar
 * Date: 20-3-18
 * Time: 20:14
 */
namespace App\Server;

use App\Kernel;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpFoundation\Request;

/**
 * This handles and executes a request and response for the Symfony kernel
 * Class HttpSocketRequestHandler
 * @package App\Server
 */
class HttpSocketRequestHandler
{
    /**
     * @var Kernel
     */
    private $kernel;
    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * HttpSocketRequestHandler constructor.
     * @param Kernel $kernel
     * @param ConsoleOutput|null $output
     */
    public function __construct(Kernel $kernel, ConsoleOutput $output = null)
    {
        $this->kernel = $kernel;
        $this->output = $output;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \React\Http\Response
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->handle($request);
    }

    /**
     * @param ConsoleOutput $output
     */
    public function setOutput(ConsoleOutput $output)
    {
        $this->output = $output;
    }

    /**
     * @return null|ConsoleOutput|Output
     */
    public function getOutput()
    {
        if (null === $this->output) {
            $this->output = new ConsoleOutput();
        }

        return $this->output;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \React\Http\Response
     */
    public function handle(ServerRequestInterface $request)
    {
        $method = $request->getMethod();
        $headers = $request->getHeaders();
        $query = $request->getQueryParams();
        $content = $request->getBody();
        $post = [];
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH']) &&
            isset($headers['Content-Type']) && (0 === strpos($headers['Content-Type'], 'application/x-www-form-urlencoded'))
        ) {
            parse_str($content, $post);
        }
        $sfRequest = new Request(
            $query,
            $post,
            [],
            [], // To get the cookies, we'll need to parse the headers
            $request->getUploadedFiles(),
            [], // Server is partially filled a few lines below
            $content
        );

        $sfRequest->setMethod($method);
        $sfRequest->headers->replace($headers);
        $sfRequest->server->set('REQUEST_URI', $request->getUri());
        $this->getOutput()->writeln('===========================================================================');
        $this->getOutput()->writeln('===========================================================================');
        $this->getOutput()->writeln('===========================================================================');
        $this->getOutput()->writeln($request->getRequestTarget());
        $this->getOutput()->writeln('===========================================================================');

        if (isset($headers['Host'])) {
            $sfRequest->server->set('SERVER_NAME', $headers['Host'][0]);
        }

        $sfResponse = $this->kernel->handle($sfRequest);
        $response = new \React\Http\Response(
            $sfResponse->getStatusCode(),
            $sfResponse->headers->all(),
            $sfResponse->getContent()
        );

        $this->kernel->terminate($sfRequest, $sfResponse);

        return $response;
    }

}