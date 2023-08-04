<?php
/**
 * elFinder Integration
 *
 * Copyright (c) 2010-2021, Alexey Sukhotin. All rights reserved.
 */

use \elFinderVolumeLocalFileSystem;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

/**
 * elFinder driver for Drupal filesystem.
 * */
class elFinderVolumeDrupal extends elFinderVolumeLocalFileSystem {

  protected $DrupalFilesACL = NULL;

  /**
   * Create Drupal file object
   *
   * @param  string $path file path
   * @return object
   * @author Alexey Sukhotin
   * */
  protected function _drupalfileobject($path) {
    $uri = $this->drupalpathtouri($path);
    return elfinder_get_drupal_file_obj($uri);
  }

  /**
   * Convert path to Drupal file URI
   *
   * @param  string $path file path
   * @return string
   * @author Alexey Sukhotin
   * */
  public function drupalpathtouri($path) {

    $relpath = $this->_relpath($path);
    $fservice = \Drupal::service('file_system');
    $pvtpath = $fservice->realpath('private://');
    $pubpath = $fservice->realpath('public://');
    $relpath = DIRECTORY_SEPARATOR !== '/' ? str_replace(DIRECTORY_SEPARATOR, '/', $relpath) : $relpath;
    $uri = '';

    $rc = strpos($path, $pvtpath);

    if ($rc == 0 && is_numeric($rc)) {
      $uri = 'private://' . substr($path, strlen($pvtpath));
    } else {
      $uri = 'public://' . substr($path, strlen($pubpath));
    }

    $position = strpos($uri, '://');
    $scheme =  $position ? substr($uri, 0, $position) : FALSE;

    $target = StreamWrapperManager::getTarget($uri);
    if ($target !== FALSE) {
      $uri = $scheme . '://' . $target;
    }

    return $uri;
  }

  /**
   * Check if file extension is allowed
   *
   * @param stdClass $file file object
   * @return array
   * @author Alexey Sukhotin
   * */
  protected function CheckExtension($file) {

    $allowed_extensions = \Drupal::config('elfinder.settings')->get('filesystem.allowed_extensions');

    if (!empty($allowed_extensions)) {

      $errors = file_validate_extensions($file, $allowed_extensions);

      if (!empty($errors)) {
        $this->setError(implode(' ', $errors));
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Create dir
   *
   * @param  string $path parent dir path
   * @param string $name new directory name
   * @return bool
   * @author Alexey Sukhotin
   * */
  protected function _mkdir($path, $name) {
    $path = $path . DIRECTORY_SEPARATOR . $name;

    if (\Drupal::service('file_system')->mkdir($path)) {
      return $path;
    }
    return FALSE;
  }

  /**
   * Create file
   *
   * @param  string $path parent dir path
   * @param string $name new file name
   * @return bool
   * @author Alexey Sukhotin
   * */
  protected function _mkfile($path, $name) {
    $path = $path . DIRECTORY_SEPARATOR . $name;
    $uri = $this->drupalpathtouri($path);

    if (!$this->CheckExtension($this->_drupalfileobject($path))) {
      return FALSE;
    }

    $newpath = file_unmanaged_save_data("", $uri);
    $file = $this->_drupalfileobject($path);
    $file->save();
    $this->FileUsageAdd($file);

    if ($file->id()) {
      return $path;
    }

    return FALSE;
  }

  /**
   * Copy file into another file
   *
   * @param  string $source source file path
   * @param  string $targetDir target directory path
   * @param  string $name new file name
   * @return bool
   * @author Alexey Sukhotin
   * */
  protected function _copy($source, $targetDir, $name) {

    $target = $targetDir . DIRECTORY_SEPARATOR . (!empty($name) ? $name : basename($source));

    if (!is_dir($target) && !$this->CheckExtension($this->_drupalfileobject($target))) {
      return FALSE;
    }

    if (!$this->CheckUserQuota()) {
      return FALSE;
    }

    if (file_copy($this->_drupalfileobject($source), $this->drupalpathtouri($target))) {
      $this->FileUsageAdd($this->_drupalfileobject($target));
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Move file into another parent dir
   *
   * @param  string $source source file path
   * @param  string $target target dir path
   * @param  string $name file name
   * @return bool
   * @author Alexey Sukhotin
   * */
  protected function _move($source, $targetDir, $name) {

    $target = $targetDir . DIRECTORY_SEPARATOR . (!empty($name) ? $name : basename($source));

    if (!is_dir($target) && !$this->CheckExtension($this->_drupalfileobject($target))) {
      return FALSE;
    }

    if (is_dir($source)) {
      $srcuri = $this->drupalpathtouri($source);
      $dsturi = $this->drupalpathtouri($target);

      $children = \Drupal::database()->select('file_managed', 'f')
        ->condition('uri', $srcuri . '/%', 'LIKE')
        ->fields('f', array('fid', 'uri'))
        ->execute()
        ->fetchAll();

      foreach ($children as $child) {
        $newuri = str_replace("$srcuri/", "$dsturi/", $child->uri);
        \Drupal::database()->update('file_managed')->fields(array('uri' => $newuri))->condition('fid', $child->fid)->execute();
      }

      return @rename($source, $target);
    } elseif (@file_move($this->_drupalfileobject($source), $this->drupalpathtouri($target))) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Remove file
   *
   * @param  string $path file path
   * @return bool
   * @author Alexey Sukhotin
   * */
  protected function _unlink($path) {

    $file = $this->_drupalfileobject($path);

    $this->FileUsageDelete($file);

    $file->delete();

    return TRUE;
  }

  /**
   * Remove dir
   *
   * @param  string $path dir path
   * @return bool
   * @author Alexey Sukhotin
   * */
  protected function _rmdir($path) {
    return \Drupal::service('file_system')->rmdir($path);
  }

  /**
   * Create new file and write into it from file pointer.
   * Return new file path or false on error.
   *
   * @param  resource $fp file pointer
   * @param  string $dir target dir path
   * @param  string $name file name
   * @return bool|string
   * @author Dmitry (dio) Levashov, Alexey Sukhotin
   * */
  protected function _save($fp, $dir, $name, $stat) {
    $tmpname = $name;

    $bu_ret = \Drupal::moduleHandler()->invokeAll('elfinder_beforeupload', array('name' => $name, 'dir' => $dir, 'stat' => $stat));

    if (isset($bu_ret)) {
      if (!is_array($bu_ret)) {
        $bu_ret = array($bu_ret);
      }

      $tmpname = end($bu_ret);
    }

    $path = $dir . DIRECTORY_SEPARATOR . (!empty($tmpname) ? $tmpname : $name);

    if (!$this->CheckUserQuota()) {
      return FALSE;
    }

    if (!$this->CheckFolderCount($dir)) {
      return FALSE;
    }

    if (!$this->CheckExtension($this->_drupalfileobject($path))) {
      return FALSE;
    }

    if (!($target = @fopen($path, 'wb'))) {
      return FALSE;
    }

    while (!feof($fp)) {
      fwrite($target, fread($fp, 8192));
    }

    fclose($target);

    @chmod($path, $this->options['fileMode']);

    $file = $this->_drupalfileobject($path);

    $file->save();

    $this->FileUsageAdd($file);

    return $path;
  }

  protected function CheckUserQuota() {
    $space = $this->CalculateUserAllowedSpace();

    if ($space == 0) {
      $this->setError(t('Quota exceeded'));
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Check file count in the folder
   *
   * @param  string $dir check path
   * @return bool
   * @author Oliver Polden (oliverpolden)
   * */
  protected function CheckFolderCount($dir) {
    $max_allowed = \Drupal::config('elfinder.settings')->get('filesystem.maxfilecount');
    if ($max_allowed > 0) {
      $options = array(
        'recurse' => FALSE,
      );
      // Match name.extension. This won't count files with no extension.
      $files = file_scan_directory($dir, '/.*\..*/', $options);

      if (count($files) >= $max_allowed) {
        $this->setError(t('Max directory file count of %count reached', array('%count' => $max_allowed)));
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Return files list in directory.
   *
   * @param  string $path dir path
   * @return array
   * @author Dmitry (dio) Levashov
   * */
  protected function _scandir($path) {
    $files = array();

    foreach (scandir($path) as $name) {
      if ($name != '.' && $name != '..') {
        $files[] = $path . DIRECTORY_SEPARATOR . $name;
      }
    }
    return $files;
  }

  public function owner($target) {
    $path = $this->decode($target);
    $user = \Drupal::currentUser();
    $file = $this->_drupalfileobject($path);

    if ($file->id()) {
      $owneraccount = $file->getOwner();

      /* AS */
      $owner = $user->getAccountName();

      $ownerformat = \Drupal::config('elfinder.settings')->get('filesystem.owner_format');

      if ($ownerformat != '') {
        $owner = token_replace($ownerformat, array('user' => $owneraccount));
      }

      return $owner;
    }
    return FALSE;
  }

  public function chown($target) {
    $path = $this->decode($target);
    $user = \Drupal::currentUser();
    $file = $this->_drupalfileobject($path);

    $file->uid = $user->id();

    $newfile = FALSE;

    if (!$file->id()) {
      $newfile = TRUE;
    }

    $file->save();

    if ($newfile) {
      $this->FileUsageAdd($file);
    }

    return TRUE;
  }


  public function uuid($target) {
    $path = $this->decode($target);
    $user = \Drupal::currentUser();
    $file = $this->_drupalfileobject($path);

    if ($file->uuid()) {
      return $file->uuid();
    }
    return FALSE;
  }

  public function fid($target) {
    $path = $this->decode($target);
    $user = \Drupal::currentUser();
    $file = $this->_drupalfileobject($path);
    
    if ($file->id()) {
      return $file->id();
    }
    return FALSE;
  }

  public static function stat_corrector(&$stat, $path, $statOwner, $volumeDriveInstance) {
    if (method_exists($volumeDriveInstance, 'fid')) {
      $stat['fid'] = (int)$volumeDriveInstance->fid($volumeDriveInstance->encode($path));
    }

    if (method_exists($volumeDriveInstance, 'owner')) {
      $stat['owner'] = $volumeDriveInstance->owner($volumeDriveInstance->encode($path));
    }

    if (method_exists($volumeDriveInstance, 'uuid')) {
      $stat['uuid'] = $volumeDriveInstance->uuid($volumeDriveInstance->encode($path));
    }
  }

  public function desc($target, $newdesc = NULL) {
    $path = $this->decode($target);

    $file = $this->_drupalfileobject($path);

    if ($file->id()) {
      $finfo = \Drupal::database()->select('elfinder_file_extinfo', 'f')
        ->condition('fid', $file->id())
        ->fields('f', array('fid', 'description'))
        ->execute()
        ->fetchObject();

      $descobj = new StdClass;
      $descobj->fid = $file->id();
      $descobj->description = $newdesc;

      if ($newdesc != NULL && user_access('edit file description')) {
        if (($rc = drupal_write_record('elfinder_file_extinfo', $descobj, isset($finfo->fid) ? array('fid') : array())) == 0) {
          return -1;
        }
      } else {
        return $finfo->description;
      }
    }
    return $newdesc;
  }

  public function downloadcount($target) {
    $path = $this->decode($target);

    $file = $this->_drupalfileobject($path);

    if ($file->id() && module_exists('elfinder_stats')) {
      $downloads = \Drupal::database()->select('elfinder_stats', 's')
        ->fields('s', array('fid'))
        ->condition('s.fid', $file->id())
        ->condition('s.type', 'download')
        ->countQuery()
        ->execute()
        ->fetchField();
      return $downloads ? $downloads : 0;
    }
    return 0;
  }

  protected function _archive($dir, $files, $name, $arc) {

    if (!$this->CheckUserQuota()) {
      return FALSE;
    }

    $ret = parent::_archive($dir, $files, $name, $arc);

    if ($ret != FALSE) {
      $file = $this->_drupalfileobject($ret);
      $file->save();
      $this->FileUsageAdd($file);
    }

    return $ret;
  }

  public function extract($hash, $makedir = NULL) {
    if (!$this->CheckUserQuota()) {
      return FALSE;
    }

    $fstat = parent::extract($hash, $makedir);

    if ($fstat != FALSE) {
      $path = $this->decode($fstat['hash']);
      $this->AddToDrupalDB($path);
    }

    return $fstat;
  }

  protected function AddToDrupalDB($path) {

    if (is_dir($path)) {
      $files = $this->_scandir($path);
      foreach ($files as $file) {
        $this->AddToDrupalDB($file);
      }
    } elseif (is_file($path)) {
      $file = $this->_drupalfileobject($path);
      $file->save();
      $this->FileUsageAdd($file);
    }
    return TRUE;
  }

  protected function CalculateUserAllowedSpace($checkuser = NULL) {
    $user = \Drupal::currentUser();

    $realUser = isset($checkuser) ? $checkuser : $user;

    $currentSpace = $this->CalculateUserUsedSpace($realUser);

    $maxSpace = isset($this->options['userProfile']->settings['user_quota']) ? parse_size($this->options['userProfile']->settings['user_quota']) : NULL;

    $diff = $maxSpace - $currentSpace;

    if (isset($maxSpace) && $maxSpace > 0) {

      if ($diff > 0) {
        return $diff;
      } else {
        return 0;
      }
    }

    return -1;
  }

  protected function CalculateUserUsedSpace($checkuser = NULL) {
    $user = \Drupal::currentUser();

    $realUser = isset($checkuser) ? $checkuser : $user;

    $q = \Drupal::database()->query("SELECT sum(filesize) FROM {file_managed} WHERE uid = :uid", array(':uid' => $realUser->id()));

    $result = $q->fetchField();

    return $result;
  }

  protected function FileUsageAdd($file) {
    // Record that the module elfinder is using the file.
    \Drupal::service('file.usage')->add($file, 'elfinder', 'file', 0); // 0 : means that there is no reference at the moment.
  }

  protected function FileUsageDelete($file) {
    // Delete record that the module elfinder is using the file.
    \Drupal::service('file.usage')->delete($file, 'elfinder', 'file', 0); // 0 : means that there is no reference at the moment.
  }

  protected function _checkArchivers() {
    parent::_checkArchivers();

    /* 2.1 caching automatically */
    /*$this->archivers = \Drupal::config('elfinder.settings')->get('misc.archivers');

    if ($this->archivers == '') {
      $this->archivers = array();
    }

    if (count($this->archivers) == 0) {
      parent::_checkArchivers();
      // FIXME: cannot save
      //\Drupal::config('elfinder.settings')->set('misc.archivers', $this->archivers);
    }*/
  }

}
