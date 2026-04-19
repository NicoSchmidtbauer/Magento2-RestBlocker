<?php

namespace JscDesign\RestBlocker\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Webapi\Exception;

class BlockFileUpload
{
    public function beforeDispatch(
        \Magento\Webapi\Controller\Rest $subject,
        RequestInterface $request
    ) {
        $path = $request->getPathInfo();

        if (
            strpos($path, '/rest/V1/guest-carts/') !== false ||
            strpos($path, '/rest/V1/carts/') !== false ||
			strpos($path, '/rest/default/V1/guest-carts/') !== false ||
			strpos($path, '/rest/default/V1/carts/') !== false
        ) {
            $content = $request->getContent();

            if (!$content) {
                return [$request];
            }

            if (strpos($content, 'file_info') !== false) {

                $data = json_decode($content, true);

                if ($this->containsFileInfo($data)) {
                    throw new Exception(
                        __('File upload via API is disabled.'),
                        0,
                        Exception::HTTP_BAD_REQUEST
                    );
                }
            }
        }

        return [$request];
    }

    private function containsFileInfo($data)
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $key => $value) {
            if ($key === 'file_info') {
                return true;
            }

            if (is_array($value) && $this->containsFileInfo($value)) {
                return true;
            }
        }

        return false;
    }
}
