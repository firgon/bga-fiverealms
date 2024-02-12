<?php

use FRMS\Core\CheatModule;

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * FiveRealms implementation : © Emmanuel Albisser <emmanuel.albisser@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * fiverealms.action.php
 *
 * FiveRealms main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/fiverealms/fiverealms/myAction.html", ...)
 *
 */


class action_fiverealms extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "fiverealms_fiverealms";
      self::trace("Complete reinitialization of board game");
    }
  }

  // public function tick()
  // {
  //   self::setAjaxMode();

  //   // Retrieve arguments
  //   // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
  //   $cell = self::getArg("cell", AT_alphanum, true);

  //   // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
  //   $this->game->actTick($cell);

  //   self::ajaxResponse();
  // }

  
  //   █████████  █████   █████ ██████████   █████████   ███████████
  //  ███░░░░░███░░███   ░░███ ░░███░░░░░█  ███░░░░░███ ░█░░░███░░░█
  // ███     ░░░  ░███    ░███  ░███  █ ░  ░███    ░███ ░   ░███  ░ 
  //░███          ░███████████  ░██████    ░███████████     ░███    
  //░███          ░███░░░░░███  ░███░░█    ░███░░░░░███     ░███    
  //░░███     ███ ░███    ░███  ░███ ░   █ ░███    ░███     ░███    
  // ░░█████████  █████   █████ ██████████ █████   █████    █████   
  //  ░░░░░░░░░  ░░░░░   ░░░░░ ░░░░░░░░░░ ░░░░░   ░░░░░    ░░░░░    
  //                                                                
  //                                                                
  //                                                                

  public function cheat()
  {
    self::setAjaxMode();
    $data = self::getArg('data', AT_json, true);
    $this->validateJSonAlphaNum($data, 'data');

    CheatModule::actCheat($data);
    self::ajaxResponse();
  }

  public function loadBugSQL()
  {
    self::setAjaxMode();
    $reportId = (int) self::getArg('report_id', AT_int, true);
    $this->game->loadBugSQL($reportId);
    self::ajaxResponse();
  }

  public function validateJSonAlphaNum($value, $argName = 'unknown')
  {
    if (is_array($value)) {
      foreach ($value as $key => $v) {
        $this->validateJSonAlphaNum($key, $argName);
        $this->validateJSonAlphaNum($v, $argName);
      }
      return true;
    }
    if (is_int($value)) {
      return true;
    }
    $bValid = preg_match("/^[_0-9a-zA-Z- ]*$/", $value) === 1;
    if (!$bValid) {
      throw new BgaSystemException("Bad value for: $argName", true, true, FEX_bad_input_argument);
    }
    return true;
  }
}
