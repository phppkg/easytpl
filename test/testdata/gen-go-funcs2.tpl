#
# usage: kite gen parse @resource-tpl/gen-by-parse/gen-go-funcs2.tpl
#
vars=[Info, Error, Warn]

###

{{ foreach ($vars as $var): }}
// {{= $var }}f print message with {{= $var }} style
func {{= $var }}f(format string, a ...interface{}) {
	{{= $var }}.Printf(format, a...)
}

{{  endforeach; }}