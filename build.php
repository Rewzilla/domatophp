<?php

$all = unserialize(file_get_contents("functions.list"));

$class_blacklist = array(
// Can't actually instantiate
	"Closure",
	"Generator",
	"HashContext",
	"RecursiveIteratorIterator",
	"IteratorIterator",
	"FilterIterator",
	"RecursiveFilterIterator",
	"CallbackFilterIterator",
	"RecursiveCallbackFilterIterator",
	"ParentIterator",
	"LimitIterator",
	"CachingIterator",
	"RecursiveCachingIterator",
	"NoRewindIterator",
	"AppendIterator",
	"InfiniteIterator",
	"RegexIterator",
	"RecursiveRegexIterator",
	"EmptyIterator",
	"RecursiveTreeIterator",
	"ArrayObject",
	"ArrayIterator",
	"RecursiveArrayIterator",
	"SplFileInfo",
	"DirectoryIterator",
	"FilesystemIterator",
	"RecursiveDirectoryIterator",
	"GlobIterator",
// ???
	"SplHeap",
	"PDOException",
	"PDO",
	"PDOStatement",
	"PDORow",
	"ReflectionFunctionAbstract",
	"ReflectionZendExtension",
// Don't have
	"ZipArchive",
	"CURLFile",
// Sooooooooo many crashes
	"XMLReader",
	"XMLWriter",
	"Phar",
	"PharData",
	"PharFileInfo",
);

$function_blacklist = array(
	"exit", // false positives
	"readline",	// pauses
	"readline_callback_handler_install", // pauses
	"syslog",	// spams syslog
	"sleep", // pauses
	"usleep", // pauses
	"time_sleep_until", // pauses
	"time_nanosleep", // pauses
	"pcntl_wait", // pauses
	"pcntl_waitstatus", // pauses
	"pcntl_waitpid", // pauses
	"pcntl_sigwaitinfo", // pauses
	"pcntl_sigtimedwait", // pauses
	"stream_socket_recvfrom", // pauses
	"posix_kill", // ends own process
	"ereg", // cpu dos
	"eregi", // cpu dos
	"eregi_replace", // cpu dos
	"ereg_replace", // cpu dos
	"similar_text", // cpu dos
	"snmpwalk", // cpu dos
	"snmpwalkoid", // cpu dos
	"snmpget", // cpu dos
	"split", // cpu dos
	"spliti", // cpu dos
	"snmpgetnext", // cpu dos
	"mcrypt_create_iv", // cpu dos
	"gmp_fact", // cpu dos
	"posix_setrlimit"
);

foreach ($all as $item) {

	if (strpos($item["name"], "::") !== false) {

		$class_name = explode("::", $item["name"])[0];
		$method_name = explode("::", $item["name"])[1];

		if (
			!class_exists($class_name) ||
			!method_exists($class_name, $method_name) ||
			in_array($class_name, $class_blacklist) ||
			!(new ReflectionClass($class_name))->getMethod($method_name)->isPublic()
		)
			continue;

		echo "<obj_${class_name}> = \$vars[\"${class_name}\"]\n";

		$params = array();
		foreach ($item["params"] as $p) {
			$params[] = "<fuzz" . $p . ">";
		}

		echo "<methodcall> = <obj_${class_name}>->${method_name}(" . implode(", ", $params) . ")\n";

	} else {

		$function_name = $item["name"];

		if (
			!function_exists($function_name) ||
			in_array($function_name, $function_blacklist)
		)
			continue;

		$params = array();
		foreach ($item["params"] as $p) {
			$params[] = "<fuzz" . $p . ">";
		}

		echo "<functioncall> = ${function_name}(" . implode(", ", $params) . ")\n";

	}

}
