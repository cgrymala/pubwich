<?php
	defined('PUBWICH') or die('No direct access allowed.');

	/**
	 * @classname Gowalla
	 * @description Get last check-ins from Gowalla
	 * @version 1.1 (20100210)
	 * @author Rémi Prévost (exomel.com)
	 * @methods GowallaUser GowallaUserStamps
	 */

	class Gowalla extends Service {

		public $base = 'http://gowalla.com';

		/**
		 * @constructor
		 */
		public function __construct( $config ) {
			$this->username = $config['username'];
			$this->setURLTemplate( $this->base.'/users/'.$config['username'].'/' );
			$this->callback_function = array( Pubwich, 'json_decode' );
			$this->http_headers = array(
				'Accept: application/json'
			);

			if ( $config['key'] ) {
				$this->http_headers[] = sprintf( 'X-Gowalla-API-Key: %s', $config['key'] );
			}

			parent::__construct( $config );
		}

	}

	class GowallaUser extends Gowalla {

		/**
		 * @constructor
		 */
		public function __construct( $config ) {
			$this->setURL( sprintf( 'http://%s:%s@api.gowalla.com/users/%s', $config['username'], $config['password'], $config['username'] ) );
			$this->setItemTemplate( '<li class="clearfix"><span class="date">{%date%}</span><a class="spot" href="{%url%}"><strong>{%name%}</strong> <img src="{%image%}" alt="" /></a><span class="comment">{%comment%}</span></li>'."\n" );
			parent::__construct( $config );
		}

		public function getData() {
			$data = parent::getData();
			return array( $data->last_visit );
		}

		public function populateItemTemplate( &$item ) {
			return array(
				'comment' => $item->comment,
				'date' => Pubwich::time_since( $item->created_at ),
				'image' => $item->spot->image_url,
				'thumbnail' => $item->spot->small_image_url,
				'name' => $item->spot->name,
				'url' => $this->base.$item->spot->url,
			);
		}

	}

	class GowallaUserStamps extends Gowalla {

		/**
		 * @constructor
		 */
		public function __construct( $config ) {
			$this->setURL( sprintf( 'http://%s:%s@api.gowalla.com/users/%s/stamps?limit=%d', $config['username'], $config['password'], $config['username'], $config['total'] ) );
			$this->setItemTemplate( '<li class="clearfix"><span class="date">{%date%}</span><a class="spot" href="{%url%}"><strong>{%name%}</strong> <img src="{%image%}" alt="" /></a><span class="comment">{%comment%}</span></li>'."\n" );
			parent::__construct( $config );
		}

		public function getData() {
			return parent::getData();
		}

		public function populateItemTemplate( &$item ) {
			return array(
				'date' => Pubwich::time_since( $item->last_visited_at ),
				'image' => $item->image_url,
				'name' => $item->name,
				'url' => $this->base.$item->spot->url,
				'visits' => $item->visits_count,
			);
		}

	}