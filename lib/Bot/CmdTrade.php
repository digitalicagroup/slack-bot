<?php

namespace Bot;

use Redmine\Client;

/**
 * Class to handle "show" commands.
 * This command will receive an array of issue numbers, will
 * gather information from a redmine project, and store its
 * results on the in the internal result attribute of
 * AbstractCommand.
 * Each issue is stored as slack attachment
 * (\Bot\SlackResultAttachment) . Unknown issues will
 * be attached at the end.
 *
 * @author Luis Augusto PeÃ±a Pereira <lpenap at gmail dot com>
 *        
 */
class CmdTrade extends AbstractCommand {
    protected $ag = "Ag";
    protected $as = "As";
    protected $ba = "Ba";
    protected $de = "De";
    protected $fl = "Fl";
    protected $hi = "Hi";
    protected $ic = "Ic";
    protected $in = "In";
    protected $lo = "Lo";
    protected $na = "Na";
    protected $ni = "Ni";
    protected $po = "Po";
    protected $ri = "Ri";
    protected $va = "Va";
    protected $wa = "Wa";
    protected $A = "A";
    protected $C = "C";
    protected $D = "D";
    protected $E = "E";
    protected $F = "F";
    protected $X = "X";

    protected $aslan = "A";
    protected $vargr = "V";
    protected $race_name = array ("A"=>"-Aslan","V"=>"-Vargr");

    protected $trades = array();
    protected $ignored = array ('-', '/',' \\', '_', ',', ' ', '.', '=');

	/**
	 * Factory method to be implemented from \Bot\AbstractCommand .
	 *
	 *
	 * Must return an instance of \Bot\SlackResult .
	 *
	 * @see \Bot\AbstractCommand::executeImpl()
	 * @return \Bot\SlackResult
	 */
	protected function executeImpl() {
		$log = $this->log;
		$result = new SlackResult ();
		
		$log->debug ( "CmdTrade: UWPs: " . implode ( ",", $this->cmd ) );
		
		$resultText = "[requested by " . $this->post ["user_name"] . "]";
		if (empty ( $this->cmd )) {
			$resultText .= " At least one UWP is required!";
		} else {
			$resultText .= " Trade information: ";
		}
		
		$attachments = array ();
		$attachmentUnknown = null;
    $first = TRUE;
    $source_trades = array();
    $source_uwp = array();
    $market_trades = array();
		foreach ( $this->cmd as $uwp ) {
			$log->debug ( "CmdTrade: processing UWP $uwp" );
			$attachment = new SlackResultAttachment ();
      $uwp_digits = str_split ($uwp);
      // cycling through uwp stats
      $current_digit = 0;
      $clean_uwp = $this->clean_uwp($uwp);
      $trade_array = $this->get_trade_classifications($clean_uwp);
      $credits = 0;
      $tc = implode (" ", $trade_array);

      if ($first) {
			  $attachment->setTitle ($this->print_uwp($clean_uwp) . "  [source]");
        $credits = $this->find_purchase_price ($trade_array, $clean_uwp);
        $attachment->setText ("$credits Cr   [$tc]");
        $source_trades = $trade_array;
        $source_uwp = $clean_uwp;
        $first = FALSE;
      } else {
			  $attachment->setTitle ($this->print_uwp($clean_uwp) . "  [market]");
        $credits = $this->find_market_price ($source_trades, $trade_array, $source_uwp, $clean_uwp);
        $attachment->setText ("$credits Cr   [$tc]");
      }
      $attachments [] = $attachment;
	  }
		
		$result->setText ( $resultText );
		$result->setAttachmentsArray ( $attachments );
		return $result;
	}

  protected function print_uwp ($uwp) {
    if (isset($uwp[8])) {
      $uwp[8] = $this->race_name[$uwp[8]];
    }
    return implode("", $uwp);
  }

  protected function clean_uwp ($uwp) {
    $digits = str_split ($uwp);
    $digits_clean = array();
    foreach ($digits as $d) {
      if (!in_array($d, $this->ignored)) {
        $digits_clean[] = strtoupper($d);
      }
    }
    $digits_clean = array_slice ($digits_clean, 0, 9);
    if (isset($digits_clean[8])){
      // check last character to si if Aslan or Vargh
      if (!(strcmp($digits_clean[8], "A")==0 || strcmp($digits_clean[8], "V")==0)) {
        array_pop($digits_clean);
      }
    }
    return $digits_clean;
  }

  protected function get_trade_classifications ($uwp) {
    $ints = array();
    foreach ($uwp as $d) {
      $ints[] = hexdec ($d);
    }
    $this->log->debug("CmdTrade: uwp: [".implode("", $uwp)."] ints: [".implode(",",$ints)."]");
    $tc = array();
    if (count($ints)>=8) {
      if ($ints[2]>=4 && $ints[2]<=9 && $ints[3]>=4 && $ints[3]<=8 && $ints[4]>=5 && $ints[4]<=7) {
        $tc[] = $this->ag;
      }
      if ($ints[1]==0 && $ints[2]==0 && $ints[3]==0) {
        $tc[] = $this->as;
      }
      if ($ints[4]==0 && $ints[5]==0 && $ints[6]==0) {
        $tc[] = $this->ba;
      }
      if ($ints[2]>=2 && $ints[3]==0) {
        $tc[] = $this->de;
      }
      if ($ints[2]>=10 && $ints[3]>=1) {
        $tc[] = $this->fl;
      }
      if ($ints[4]>=9) {
        $tc[] = $this->hi;
      }
      if ($ints[2]<=1 && $ints[3]>=1) {
        $tc[] = $this->ic;
      }
      if (($ints[2]<=2 || $ints[2]==4 || $ints[2]==7 || $ints[2]==9) && $ints[4]>=9) {
        $tc[] = $this->in;
      }
      if ($ints[4]<=3) {
        $tc[] = $this->lo;
      }
      if ($ints[2]<=3 && $ints[3]<=3 && $ints[4]>=6) {
        $tc[] = $this->na;
      }
      if ($ints[4]<=6) {
        $tc[] = $this->ni;
      }
      if ($ints[2]>=2 && $ints[2]<=5 && $ints[3]<=3) {
        $tc[] = $this->po;
      }
      $flag = 0;
      if (isset($uwp[8])) {
        // check if aslan
        if (strcmp($uwp[8], "A")==0) {
          if ($ints[2]>=6 && $ints[2]<=8 && $ints[4]>=6 && $ints[4]<=8) {
            $tc[] = $this->ri;
          }
          $flag = 1;
        }
        // vargh world
        if (strcmp($uwp[8], "V")==0) {
          if ($ints[2]>=6 && $ints[2]<=8 && $ints[4]>=6 && $ints[4]<=8 && (($ints[5]>=4 && $ints[5]<=6) || $ints[5]==8 || $ints[5]==9)) {
            $tc[] = $this->ri;
          }
          $flag = 1;
        }
      }

      if ($flag==0) {
        if ($ints[2]>=6 && $ints[2]<=8 && $ints[4]>=6 && $ints[4]<=8 && $ints[5]>=4 && $ints[5]<=9) {
          $tc[] = $this->ri;
        }
      }
      if ($ints[2]==0) {
        $tc[] = $this->va;
      }
      if ($ints[3]==10) {
        $tc[] = $this->wa;
      }
    }
    return $tc;
  }

  protected function find_purchase_price ($tc, $uwp) {
    $result = 0;
    if (count ($uwp)>=8) {
      $trades[$this->ag] = -1;
      $trades[$this->as] = -1;
      $trades[$this->ba] = 1;
      $trades[$this->de] = 1;
      $trades[$this->fl] = 1;
      $trades[$this->hi] = -1;
      $trades[$this->ic] = 0;
      $trades[$this->in] = -1;
      $trades[$this->lo] = 1;
      $trades[$this->na] = 0;
      $trades[$this->ni] = 1;
      $trades[$this->po] = -1;
      $trades[$this->ri] = 1;
      $trades[$this->va] = 1;
      $trades[$this->wa] = 0;
      $sp[$this->A] = -1;
      $sp[$this->C] = 1;
      $sp[$this->D] = 2;
      $sp[$this->E] = 3;
      $sp[$this->F] = 4;
      $sp[$this->X] = 5;
      $mod = 0;
      foreach ($tc as $t) {
        $mod += $trades[$t];
      }
      $spmod = 0;
      if (isset ($sp[$uwp[0]])) {
        $spmod = $sp[$uwp[0]];
      }
      $tlmod = hexdec($uwp[7]) * 100;
      $result = 4000 + ($mod*1000) + ($spmod*1000) + $tlmod;
      $this->log->debug("CmdTrade: price: $result , tcmods: $mod , spmod($uwp[0]): $spmod , tlmod($uwp[7]): $tlmod");
    }
    return $result;
  }

  protected function find_market_price ($source, $market, $source_uwp, $market_uwp) {
    $result = 0;
    if (count ($source_uwp)>=8 && count ($market_uwp)>=8) {
      $trades[$this->ag] = 0;
      $trades[$this->as] = 1;
      $trades[$this->ba] = 2;
      $trades[$this->de] = 3;
      $trades[$this->fl] = 4;
      $trades[$this->hi] = 5;
      $trades[$this->ic] = 6;
      $trades[$this->in] = 7;
      $trades[$this->lo] = 8;
      $trades[$this->na] = 9;
      $trades[$this->ni] = 10;
      $trades[$this->po] = 11;
      $trades[$this->ri] = 12;
      $trades[$this->va] = 13;
      $trades[$this->wa] = 14;;
      $trading[0] =  array(1,1,0,1,0,1,0,1,1,1,0,0,1,0,0); //ag
      $trading[1] =  array(0,1,0,0,0,0,0,1,0,1,0,0,0,0,0); //as
      $trading[2] =  array(1,0,0,0,0,0,0,1,0,0,0,0,0,0,0); //ba
      $trading[3] =  array(0,0,0,1,0,0,0,0,0,1,0,0,0,0,0); //de
      $trading[4] =  array(0,0,0,0,1,0,0,1,0,0,0,0,0,0,0); //fl
      $trading[5] =  array(0,0,0,0,0,1,0,0,1,0,0,0,1,0,0); //hi
      $trading[6] =  array(0,0,0,0,0,0,0,1,0,0,0,0,0,0,0); //ic
      $trading[7] =  array(1,1,0,1,1,1,0,1,0,0,1,1,1,1,1); //in
      $trading[8] =  array(0,0,0,0,0,0,0,1,0,0,-1,0,0,0,0); //lo
      $trading[9] =  array(0,1,0,1,0,0,0,0,0,0,0,1,0,0,0); //na
      $trading[10] = array(0,0,0,0,0,0,0,1,0,0,-1,-1,0,0,0); //ni
      $trading[11] = array(0,0,0,0,0,0,0,0,0,0,0,-1,0,0,0); //po
      $trading[12] = array(1,0,0,1,0,1,0,1,0,1,0,0,1,0,0); //ri
      $trading[13] = array(1,0,0,0,0,0,0,1,0,0,0,0,1,0,0); //va
      $trading[14] = array(0,0,0,0,0,0,0,1,0,0,0,0,1,0,1); //wa
      $mods = 0;
      foreach ($source as $s) {
        foreach ($market as $m) {
          $i = $trades[$s];
          $j = $trades[$m];
          $mod = $trading[$i][$j];
          $this->log->debug("CmdTrade: source: $s , market: $m , mod: $mod");
          $mods += $mod;
        }
      }
      $result = 5000 + ($mods*1000);
      $tlmod = (hexdec($source_uwp[7]) - hexdec($market_uwp[7]))/10;
      $aux = $tlmod * $result;
      $result += $aux;
      $this->log->debug("CmdTrade: market $result , mods: $mods , tlmod: $tlmod");
    }
    return $result;
  }
}
