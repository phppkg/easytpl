
{{ foreach ($vars as $var): }}
// {{= $var }}f print message with {{= $var }} style
func {{= $var }}f(format string, a ...interface{}) {
	{{= $var }}.Printf(format, a...)
}

{{  endforeach; }}
