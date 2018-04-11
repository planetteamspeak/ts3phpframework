<?php

namespace TeamSpeak3\Configuration\Authentication;

interface TokenInterface
{
  public function getToken(): string;
}