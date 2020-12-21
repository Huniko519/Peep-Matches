<?php

final class UTIL_Debug
{
    private static $pvOutput;
    private static $pvObjects;
    private static $pvDepth = 10;

    public static function varDump( $var, $exit = false )
    {
        self::addDebugStyles();

        self::$pvOutput = '';
        self::$pvObjects = array();
        self::dumper($var, 0);

        $debugString = '
    	<div class="peep_debug_cont">
    		<div class="peep_debug_body">
    			<div class="peep_debug_cap vardump">PEEP Debug - Vardump</div>
    			<div>
    				<pre class="vardumper">' . self::$pvOutput .
            "\n\n" . '<b>Type:</b> <span style="color:red;">' . ucfirst(gettype($var)) . "</span>" . '
    				</pre>
    			</div>
    		</div>
    	</div>
    	';

        echo $debugString;

        if ( $exit )
        {
            exit;
        }
    }

    private static function dumper( $var, $level )
    {
        switch ( gettype($var) )
        {
            case 'boolean':
                self::$pvOutput .= '<span class="bool">' . ( $var ? 'true' : 'false' ) . '</span>';
                break;

            case 'integer':
                self::$pvOutput .= '<span class="number">' . $var . '</span>';
                break;

            case 'double':
                self::$pvOutput .= '<span class="number">' . $var . '</span>';
                break;

            case 'string':
                self::$pvOutput .= '<span class="string">' . htmlspecialchars($var) . '</span>';
                break;

            case 'resource':
                self::$pvOutput .= '{resource}';
                break;

            case 'NULL':
                self::$pvOutput .= '<span class="null">null</span>';
                break;

            case 'unknown type':
                self::$pvOutput .= '{unknown}';
                break;

            case 'array':
                if ( self::$pvDepth <= $level )
                {
                    self::$pvOutput .= '<span class="array">array(...)</span>';
                }
                else if ( empty($var) )
                {
                    self::$pvOutput .= '<span class="array">array()</span>';
                }
                else
                {
                    $keys = array_keys($var);
                    $spaces = str_repeat(' ', ($level * 4));
                    self::$pvOutput .= '<span class="array">array</span>' . "\n" . $spaces . '(';

                    foreach ( $keys as $key )
                    {
                        self::$pvOutput .= "\n" . $spaces . "    [" . $key . "] => ";
                        self::$pvOutput .= self::dumper($var[$key], ($level + 1));
                    }
                    self::$pvOutput .= "\n" . $spaces . ')';
                }
                break;

            case 'object':
                if ( ( $id = array_search($var, self::$pvObjects, true)) !== false )
                {
                    self::$pvOutput .= get_class($var) . '#' . ($id + 1) . '(...)';
                }
                else if ( self::$pvDepth <= $level )
                {
                    self::$pvOutput .= get_class($var) . '(...)';
                }
                else
                {
                    $id = array_push(self::$pvObjects, $var);
                    $className = get_class($var);
                    $members = (array) $var;
                    $keys = array_keys($members);
                    $spaces = str_repeat(' ', ($level * 4));
                    self::$pvOutput .= '<span class="class">' . "$className</span>#$id\n" . $spaces . '(';

                    foreach ( $keys as $key )
                    {
                        $keyDisplay = strtr(trim($key) . '</span>', array("\0" => ':<span class="class_prop">'));
                        self::$pvOutput .= "\n" . $spaces . "    [$keyDisplay] => ";
                        self::$pvOutput .= self::dumper($members[$key], ($level + 1));
                    }

                    self::$pvOutput .= "\n" . $spaces . ')';
                }
                break;
        }
    }

    public static function printDebugMessage( $data )
    {
        self::addDebugStyles();

        $debugString = '
    		<div class="peep_debug_cont">
    			<div class="peep_debug_body">
    				<div class="peep_debug_cap ' . strtolower($data['type']) . '">PEEP Debug - ' . $data['type'] . '</div>
    				<table>
    					<tr>
    						<td class="lbl">Message:</td>
    						<td class="cnt">' . $data['message'] . '</td>
    					</tr>
    					<tr>
    						<td class="lbl">File:</td>
    						<td class="cnt">' . $data['file'] . '</td>
    					</tr>
    					<tr>
    						<td class="lbl">Line:</td>
    						<td class="cnt">' . $data['line'] . '</td>
    					</tr>
                        ' . (!empty($data['trace']) ?
                '<tr>
    						<td class="lbl">Trace:</td>
    						<td class="cnt"><pre>' . $data['trace'] . '</pre></td>
    					</tr>
                        ' : '' ) .
            (!empty($data['class']) ?
                '<tr>
    						<td class="lbl">Type:</td>
    						<td class="cnt" style="color:red;">' . $data['class'] . '</td>
    					</tr>
                        ' : '' ) . '
    				</table>
    			</div>
    		</div>
    		';

        echo $debugString;
    }

    private static function addDebugStyles()
    {
        echo '
    	<style>
    		.peep_debug_cont{padding:15px 0;width:80%;margin:0 auto;}
    		.peep_debug_body{background:#fff;border:4px double;padding:5px;}
    		.peep_debug_cap{font:bold 13px Tahoma;color:#fff;padding:5px;border:1px solid #000;width:250px;margin-top:-20px;}
    		.peep_debug_body .notice{background:#fdf403;color:#555;}
    		.peep_debug_body .warning{background:#f8b423;color:#555;}
    		.peep_debug_body .error{background:#c10505;color:#fff;}
    		.peep_debug_body .exception{background:#093dd3;color:#fff;}
    		.peep_debug_body .vardump{background:#333;color:#fff;}
    		.vardumper .string{color:green}
    		.vardumper .null{color:blue}
    		.vardumper .array{color:blue}
            .vardumper .bool{color:blue}
    		.vardumper .property{color:brown}
    		.vardumper .number{color:red}
            .vardumper .class{color:black;}
            .vardumper .class_prop{color:brown;}
    	</style>
    	';
    }
}
