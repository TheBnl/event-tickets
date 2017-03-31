<?php
/*
class EventReservation extends DataObject{

	private static $db = array(
		'Name'=>'Varchar(255)',
		'Email'=>'Varchar(255)',
		'Telephone'=>'Varchar(255)',
		'Organisation'=>'Varchar(255)',
		'Function'=>'Varchar(255)',
		'Persons'=>'Int',
		'Comments'=>'Text',
		'ReservationDate'=>'SS_DateTime',
		'ReservationNumber'=>'Varchar(255)',
		'ActivationHash'=>'Varchar(255)',
		'Status'=>'Enum("pending,confirmed,canceled","pending")'
	);

	private static $has_one = array(
		'Event'=>'CalendarEvent',
		'Member'=>'Member'
	);

	private static $summary_fields = array(
		'ReservationDate.Nice'=>'Datum',
		'ReservationNumber'=>'Nummer',
		'Name'=>'Naam',
		'Email'=>'E-mail',
		'Persons'=>'Persons',
		'Status'=>'Status'
	);

	public function sendMessageToClient(){
		$subject = 'Uw reservering voor ' . $this->Event()->Title;
		$from = 'reserveringen@nieuwspoort.nl';
		$to = $this->Email;
		$email = new Email($from,$to,$subject);
		$email->setTemplate('EventReservationMessageClient');
		$data = array(
			'Reservation'=>$this
		);
		$email->populateTemplate($data);

		echo($email->debug());
		die();

	}

	public function generateActivationHash(){
		return md5(uniqid(rand(), true));
	}

	public function generateReservationNumber(){
		$event = $this->Event();
		$startdate = $event->myStartDate();
		$date = new DateTime( $startdate );
		return strftime("%Y%m%d", $date->getTimestamp()) . '.' . $event->ID . '.' . $this->ID;
	}


	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if( !$this->ActivationHash ){
			$this->ActivationHash = $this->generateActivationHash();
		}
	}

	public function onAfterWrite(){
		parent::onAfterWrite();
		if( !$this->ReservationNumber ){
			$this->ReservationNumber = $this->generateReservationNumber();
			$this->write();
		}

	}

}*/