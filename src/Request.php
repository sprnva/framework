<?php

namespace App\Core;

use App\Core\App;


class Request
{
	/**
	 * Fetch the request URI.
	 *
	 * @return string
	 */
	public static function uri()
	{
		$base_uri = (App::get('base_url') != "")
			? str_replace(App::get('base_url'), "", $_SERVER['REQUEST_URI'])
			: $_SERVER['REQUEST_URI'];

		return parse_url($base_uri, PHP_URL_PATH);
	}

	/**
	 * Fetch the request method.
	 *
	 * @return string
	 */
	public static function method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Validates the POST method inputs
	 * check if the input has validation set.
	 * 
	 */
	public static function validator($datas = [])
	{
		$errorList = [];
		foreach ($datas as $key => $data) {
			foreach ($data as $types) {
				$type = explode(':', $types);

				switch ($type[0]) {
					case 'required':
						if ($_REQUEST[$key] == "") {
							$errorList[] = "&bull; {$key} is {$type[0]} but has no value.";
						}

						break;

					case 'min':
						if (strlen($_REQUEST[$key]) < $type[1]) {
							$errorList[] = "&bull; {$key} is less than {$type[1]} character/s.";
						}

						break;

					case 'max':
						if (strlen($_REQUEST[$key]) > $type[1]) {
							$errorList[] = "&bull; {$key} is greater than {$type[1]} character/s.";
						}

						break;

					case 'email':
						if (strpos('@', $_REQUEST[$key]) !== false) {
							$errorList[] = "&bull; {$key} is not a valid email address.";
						}

						break;

					case 'unique':

						$param = explode(",", $type[1]);
						$table = $param[0];
						if ($table != "") {

							$column = $param[1];
							$except = $param[2];
							$idColumn = $param[3];

							// if unique:{table}
							if (empty($column) && empty($except) && empty($idColumn)) {
								$newColumn = $key;
								$query = "`{$newColumn}` = '$_REQUEST[$key]'";
							}

							// if unique:{table},{column}
							if (!empty($column) && empty($except) && empty($idColumn)) {
								$newColumn = $column;
								$query = "`{$newColumn}` = '$_REQUEST[$key]'";
							}

							// if unique:{table},{exept},{id}
							if (empty($idColumn) && !empty($column) && !empty($except)) {

								$newExcept = $column;
								$newIdColumn = $except;

								$query = "`{$key}` = '$_REQUEST[$key]' AND `{$newExcept}` != '{$newIdColumn}'";
							}

							// if unique:{table},{column},{exept},{id}
							if (!empty($column) && !empty($except) && !empty($idColumn)) {
								$query = "`{$column}` = '$_REQUEST[$key]' AND `{$except}` != '{$idColumn}'";
							}

							$response = DB()->select("count(*) as '{$table}_count'", $param[0], $query)->get();

							if ($response["{$table}_count"] > 0) {
								$errorList[] = "&bull; {$key} already exist in database.";
							}
						}

						break;

					case 'boolean':

						$bool = ['true', 'false', '1', '0'];
						if (!in_array($_REQUEST[$key], $bool)) {
							$errorList[] = "&bull; {$key} is not a boolean.";
						}

						break;

					default:
						break;
				}
			}
		}

		return $errorList;
	}

	/**
	 * Validates the POST method inputs
	 * 
	 */
	public static function validate($uri = '', $datas = [])
	{
		$errorList = static::validator($datas);

		foreach ($_REQUEST as $key => $value) {
			$post_data[$key] = sanitizeString($value);

			if ($key != 'password') {
				$setOldInput[$key] = sanitizeString($value);
			}
		}

		static::storeValidatedToSession($setOldInput);

		if (!empty($errorList)) {
			redirect($uri, ["message" => implode('<br>', $errorList), "status" => "danger"]);
		}

		if (isset($_REQUEST['csrf_token'])) {
			static::verifyCsrfToken($_REQUEST['csrf_token']);
		}

		if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
			static::verifyCsrfToken($_REQUEST['csrf_token']);
		}

		return $post_data;
	}

	/**
	 * store validated request to session
	 * 
	 */
	private static function storeValidatedToSession($validatedRequest)
	{
		static::invalidateOld();
		$_SESSION['OLD'] = $validatedRequest;
	}

	/**
	 * get the old input values
	 * 
	 */
	public static function old($inputName)
	{
		return (!empty($inputName) && isset($_SESSION['OLD']) && array_key_exists($inputName, $_SESSION['OLD']))
			? $_SESSION['OLD'][$inputName]
			: '';
	}

	/**
	 * generate a token
	 * 
	 */
	public static function token($length)
	{
		return md5(bin2hex(randChar($length)));
	}

	/**
	 * will send an email containing the password reset link
	 * 
	 */
	public static function passwordResetLink($request)
	{
		$isEmailExist = DB()->select("*", "users", "email = '" . $request['email'] . "'")->get();

		if (!$isEmailExist) {
			redirect('/forgot/password', ["message" => 'E-mail not found in the server.', "status" => 'danger']);
		} else {

			$token = Request::token(10);

			$subject = "Sprnva password reset link";
			$emailTemplate = file_get_contents('vendor/sprnva/framework/src/Email/stubs/email.stubs');

			$app_name = ["{{app_name}}", "{{username}}", "{{link_token}}", "{{year}}"];
			$values = [
				App::get('config')['app']['name'],
				$isEmailExist['fullname'],
				$_SERVER['SERVER_NAME'] . "/" . App::get('config')['app']['base_url'] . "/reset/password/" . $token,
				date('Y')
			];
			$body_content = str_replace($app_name, $values, $emailTemplate);

			$body = $body_content;

			$isSent = sendMail($subject, $body, $request['email']);

			if ($isSent[1] == "success") {

				$insertData = [
					'email' => $request['email'],
					'token' => $token,
					'created_at' => date("Y-m-d H:i:s")
				];

				$hasResetPending = DB()->select("email", "password_resets", "email = '" . $request['email'] . "'")->get();

				if (!empty($hasResetPending['email'])) {
					DB()->update('password_resets', $insertData, "email = '" . $request['email'] . "'");
				} else {
					DB()->insert('password_resets', $insertData);
				}
			}

			redirect('/forgot/password', $isSent);
		}
	}

	/**
	 * generates a csrf token
	 * 
	 */
	public static function csrf_token()
	{
		$token = "";

		if (!isset($_SESSION["_sprnva_token_"])) {
			$_SESSION["_sprnva_token_"] = Request::token(10);
		} else {
			$token = $_SESSION["_sprnva_token_"];
		}

		return $token;
	}

	/**
	 * generates a secret csrf token
	 * for form submit
	 * 
	 */
	public static function csrf()
	{
		return bcrypt(static::csrf_token());
	}

	/**
	 * verifies csrf token
	 * match secret token vs users token
	 * 
	 */
	public static function verifyCsrfToken($request)
	{
		if (!checkHash($_SESSION["_sprnva_token_"], $request)) {
			throwException('419 | Page expired.');
		}
	}

	/**
	 * renew the csrf token
	 * 
	 */
	public static function renewCsrfToken()
	{
		$_SESSION["_sprnva_token_"] = Request::token(10);
	}

	/**
	 * unset old datas
	 * 
	 */
	public static function invalidateOld()
	{
		if (isset($_SESSION['OLD'])) {
			unset($_SESSION['OLD']);
		}
	}

	/**
	 * Determine if the uploaded data contains a file.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public static function hasFile($key)
	{
		if ($_FILES[$key]['size'] == 0 && $_FILES[$key]['error'] == 0) {
			return false;
		}

		return true;
	}

	/**
	 * Write the contents of a file.
	 *
	 * @param  string  $path
	 * @param  string  $contents
	 * @param  bool  $lock
	 * @return int|bool
	 */
	public static function storeAs($file_tmp, $temp_dir, $type, $filename, $folder = '')
	{
		Filesystem::noMemoryLimit();

		$data = Filesystem::get($file_tmp);
		$imagedata = 'data:' . $type . ';base64,' . base64_encode($data);

		$tmp_folder = $temp_dir;

		static::ensureUploadsAndTmpFolderExist();
		Filesystem::makeDirectory($tmp_folder . $folder);

		$path = $tmp_folder . $folder . '/' . $filename;

		list($type, $imagedata) = explode(';', $imagedata);
		list(, $imagedata) = explode(',', $imagedata);

		$imagedata = base64_decode($imagedata);

		Filesystem::put($path, $imagedata);
	}

	public static function ensureUploadsAndTmpFolderExist()
	{
		// ensure uploads directory exist
		Filesystem::makeDirectory("public/assets/uploads");

		// ensure tmp directory exist
		Filesystem::makeDirectory("public/assets/uploads/tmp");
	}
}
