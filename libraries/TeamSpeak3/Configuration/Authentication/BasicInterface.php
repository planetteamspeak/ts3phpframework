<?php

namespace TeamSpeak3\Configuration\Authentication;

interface BasicInterface
{
  /**
   * Get identification string for authorization.
   * 
   * @return string user, account, ID or other identification
   */
  public function getUsername() : string;
  
  /**
   * Get secret for authorization.
   * 
   * @return string password, key, or other secret
   */
  public function getPassword() : string;
}