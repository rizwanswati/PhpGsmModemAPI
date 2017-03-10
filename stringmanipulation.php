<?php
class stringmanipulation
{
    function strafter($string, $substring)
    {
        $pos = strpos($string, $substring);
        if ($pos === false)
            return $string;
        else
            return (substr($string, $pos + strlen($substring)));
    }

    function strbefore($string, $substring)
    {
        $pos = strpos($string, $substring);
        if ($pos === false)
            return $string;
        else
            return (substr($string, 0, $pos));
    }

    function removefirsttwoindex($lines)
    {
        unset($lines[0]);
        unset($lines[1]);
        $lines = array_values($lines);

        for ($i = 0; $i < count($lines); $i++) {
            if ($lines[$i] == '') {
                unset($lines[$i]);
                $lines = array_values($lines);
            }
        }
        $len = count($lines);
        unset($lines[$len - 1]);
        $lines = array_values($lines);
        print_r($lines);
        return $lines;
    }

    function ShowMessage($lines)
    {
        for ($i = 0; $i < count($lines); $i = $i + 2) {
            $str = $lines[$i];
            $str .= $lines[$i + 1];
            $str = strafter($str, ',');
            $str = strafter($str, ',');

            $phone = strbefore($str, ',');
            $str = $lines[$i];
            $str .= $lines[$i + 1];

            for ($j = 0; $j < 3; $j++) {
                $str = strafter($str, ',');
            }

            $str = substr($str, 4);
            $dateTime = strbefore($str, '"');
            $messageText = strafter($str, '"');

            echo $phone . '<br/>' . $messageText . '<br/>' . $dateTime . '<hr/>';

        }
    }
}

?>