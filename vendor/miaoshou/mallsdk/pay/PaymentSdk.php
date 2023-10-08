<?php
namespace nisiya;

namespace nisiya\mallsdk\pay;

use nisiya\mallsdk\CommonSdk;

class PaymentSdk extends CommonSdk
{
    public function gotopay($params)
    {
        return $this->send($params, __METHOD__);
    }

}
