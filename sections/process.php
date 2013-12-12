<?php
	# Status variables
	$loggedIn = false;
	$signedIn = false;

	# Process log in
	if ( isset( $_POST['login'] ) 
	&& empty( $_POST['login'] ) 
	&& !empty( $_POST['username'] ) 
	&& !empty( $_POST['password'] ) ) :
		$user = addslashes( htmlspecialchars( $_POST['username'] ) );
		$passwd = addslashes( htmlspecialchars( $_POST['password'] ) );
		$_SESSION['user'] = new User(User::login( $user, $passwd ));
		$_SESSION['online'] = true;
		$loggedIn = true;
	endif;

	# Process log out
	if ( isset( $_POST['logout'] )
	&& empty( $_POST['logout'] ) ) :
		User::logout();
		header("Location: ./");
	endif;

	# Process sign in
	if ( isset( $_POST['signin'] ) 
	&& empty( $_POST['signin'] ) 
	&& !empty( $_POST['username'] ) 
	&& !empty( $_POST['email'] ) 
	&& !empty( $_POST['password'] ) 
	&& !empty( $_POST['password-confirm'] ) 
	&& $_POST['password'] === $_POST['password-confirm'] ) :
		$user = addslashes( htmlspecialchars( $_POST['username'] ) );
		$email = addslashes( htmlspecialchars( $_POST['email'] ) );
		$passwd = addslashes( htmlspecialchars( $_POST['password'] ) );
		User::create( $user, $email, $passwd );
		$_SESSION['user'] = new User(User::login( $user, $passwd ));
		$_SESSION['online'] = true;
		$signedIn = true;
	endif;

	# Process regular search
	/**
	 * @author Jérôme Boesch
	 * 
	 * @todo accents, no result
	 */
	if ( isset($_GET['q']) && (!empty( $_GET['q'])) ):
		$db = $_SESSION['db'];
		$q = $_GET['q'];

		$search_songs = array();
		$search_albums = array();
		$search_artists = array();
		$search_users = array();

		# Songs
		$stmt = $db->prepare( "select id from song where title like ? order by title;" );
		$stmt->execute( array(
			'%'.$q.'%'
		) );

		while ($song_result = $stmt->fetch(PDO::FETCH_NUM)) {
			$search_songs[] = new Song($song_result[0]);
		}

		$stmt->closeCursor();

		# Albums
		$stmt = $db->prepare( "select id from album where name like ? order by name;" );
		$stmt->execute( array(
			'%'.$q.'%'
		) );

		while ($search_albums_result = $stmt->fetch(PDO::FETCH_NUM)) {
			$search_albums[] = new Album($search_albums_result[0]);
		}

		$stmt->closeCursor();


		# Artist
		$stmt = $db->prepare( "select id from artist where name like ? order by name;" );
		$stmt->execute( array(
			'%'.$q.'%'
		) );

		while ($search_artists_result = $stmt->fetch(PDO::FETCH_NUM)) {
			$search_artists[] = new Artist($search_artists_result[0]);
		}

		$stmt->closeCursor();


		# Users
		$stmt = $db->prepare( "select id from user where username like ? order by username;" );
		$stmt->execute( array(
			'%'.$q.'%'
		) );

		while ($search_users_result = $stmt->fetch(PDO::FETCH_NUM)) {
			$search_users[] = new User($search_users_result[0]);
		}

		$stmt->closeCursor();
	endif;

	/**
	 * @author Jérôme Boesch 
	 *
	 */
	if( isset( $message ) && isset( $text ) && !empty( $text ) ):

		
		$user = $_SESSION['user'];
		$id = $user->getId();
		$reason = $_POST['reason'];
		$text = $_POST['text'];
		$read = 0;
		$date = date('d-m-y');

		$stmt->prepare( "insert into message (id, user, text, date, read, reason) values (:id, :user, :text, unix_timestamp(), :read, :reason);" );
		$stmt->execute( array(
			':id' => $id,
			':user' => $user,
			':text' => $text,
			':read' => $read,
			':reason' => $reason
		) );

		$stmt->closeCursor();
	endif;
