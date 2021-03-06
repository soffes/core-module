<?php defined('SYSPATH') or die('No direct script access.');
/**
 * memcached
 *
 * @package core
 * @author Josh Turmel
 *
 * This class converts data to and from different formats
 *
 **/
class memcached_Core {

	private static $config_setup = FALSE;

	private static $host;
	private static $port;
	private static $set_flag;
	private static $set_expire;
	private static $delete_timeout;

	public static function connect()
	{
		if (self::$config_setup === FALSE)
		{
			self::$host           = Kohana::config('memcached.host');
			self::$port           = Kohana::config('memcached.port');
			self::$set_flag       = Kohana::config('memcached.set_flag');
			self::$set_expire     = Kohana::config('memcached.set_expire');
			self::$delete_timeout = Kohana::config('memcached.delete_timeout');

			self::$config_setup = TRUE;
		}

		return memcache_connect(self::$host, self::$port);
	}

	public static function build_key()
	{
		$args = func_get_args();

		$key = '';

		foreach ($args as $i => $arg)
		{
			if (is_array($arg) === TRUE)
			{
				if (count($arg) > 0)
				{
					array_walk($arg, array('self', 'array_value_to_str'));
					$key .= ($i === 0) ? implode('_', $arg) : ('_' . implode('_', $arg));
				}
			}
			else
			{
				$key .= ($i === 0) ? (string) $arg : ('_' . (string) $arg);
			}
		}

		return mb_strtolower($key);
	}

	public static function get($keys)
	{
		return self::connect()->get($keys);
	}

	public static function set($key, $value, $expire = FALSE, $flag = FALSE)
	{
		$expire = ($expire === FALSE) ? self::$set_expire : (int) $expire;
		$flag   = ($flag === FALSE) ? self::$set_flag : $flag;

		return self::connect()->set($key, $value, $flag, $expire);
	}

	public static function delete($key, $timeout = FALSE)
	{
		$timeout = ($timeout === FALSE) ? self::$delete_timeout : (int) $timeout;
		return self::connect()->delete($key, $timeout);
	}

	public static function tag_add($tag, $keys)
	{
		return self::connect()->tag_add(mb_strtolower($tag), $keys);
	}

	public static function tags_add($key, $tag)
	{
		$tags = (is_array($tag) === TRUE) ? $tag : array_slice(func_get_args(), 1);

		foreach ($tags as $tag)
		{
			self::connect()->tag_add(mb_strtolower($tag), $key);
		}

		return TRUE;
	}

	public static function tags_delete($tags)
	{
		if (is_array($tags) === TRUE)
		{
			array_walk($tags, array('self', 'mb_strtolower_array'));
		}
		else
		{
			$tags = mb_strtolower($tags);
		}

		return self::connect()->tags_delete($tags);
	}

	private static function array_value_to_str(&$value, $key)
	{
		$value = bool::to_string($value);
	}

	private static function mb_strtolower_array(&$value, $key)
	{
		if (is_array($value) === TRUE)
		{
			$value = implode('-', $value);
		}

		$value = mb_strtolower($value);
	}
}