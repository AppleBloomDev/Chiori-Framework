<?php namespace Milky\Http;

use Milky\Impl\Arrayable;
use Milky\Impl\Jsonable;
use InvalidArgumentException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\JsonResponse as BaseJsonResponse;

class JsonResponse extends BaseJsonResponse
{
    use ResponseTrait;

    /**
     * Constructor.
     *
     * @param  mixed  $data
     * @param  int    $status
     * @param  array  $headers
     * @param  int    $options
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0)
    {
        $this->encodingOptions = $options;

        parent::__construct($data, $status, $headers);
    }

    /**
     * Get the json_decoded data from the response.
     *
     * @param  bool  $assoc
     * @param  int   $depth
     * @return mixed
     */
    public function getData($assoc = false, $depth = 512)
    {
        return json_decode($this->data, $assoc, $depth);
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        if ($data instanceof Arrayable) {
            $this->data = json_encode($data->toArray(), $this->encodingOptions);
        } elseif ($data instanceof Jsonable) {
            $this->data = $data->toJson($this->encodingOptions);
        } elseif ($data instanceof JsonSerializable) {
            $this->data = json_encode($data->jsonSerialize(), $this->encodingOptions);
        } else {
            $this->data = json_encode($data, $this->encodingOptions);
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $this->update();
    }

    /**
     * Get the JSON encoding options.
     *
     * @return int
     */
    public function getJsonOptions()
    {
        return $this->getEncodingOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function setEncodingOptions($encodingOptions)
    {
        return $this->setJsonOptions($encodingOptions);
    }

    /**
     * Set the JSON encoding options.
     *
     * @param  int  $options
     * @return mixed
     */
    public function setJsonOptions($options)
    {
        $this->encodingOptions = (int) $options;

        return $this->setData($this->getData());
    }
}
