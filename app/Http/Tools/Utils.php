<?php

namespace App\Http\Tools;

class Utils
{
    static public function jsonResponse ($data = null, $code = StringConstants::Code_Succeed, $msg = 'ok', $bizCode = null)
    {
        $data = Utils::replaceAllNullToEmptyString($data);

        return response()->json(Utils::pack($data, $code, $msg, $bizCode));
    }


    /**
     * @param $data
     * @param int $code
     * @param string $msg
     * @param null $bizCode 业务状态码（主要给前端判断是否需要上报告警：0表示不需要上报异常告警，非0表示需要上报异常告警）
     * @return array
     */
    static public function pack ($data, $code = StringConstants::Code_Succeed, $msg = StringConstants::Msg_Succeed, $bizCode = null)
    {
        $package = [
            'code'   => $code,
            'msg' => $msg,
            'data'   => $data,
            'bizCode'   => $bizCode === null ? $code : $bizCode, //没特别指明，默认bizCode等于code
        ];

        return $package;
    }


    static function replaceAllNullToEmptyString ($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[ $key ] = Utils::replaceAllNullToEmptyString($value);
            }
        } else if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = Utils::replaceAllNullToEmptyString($value);
            }
        } else if ($data === null) {
            $data = '';
        }
        return $data;
    }

}
