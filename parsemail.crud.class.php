<?php
/**
 * 
 * @author USER
 *
 */
class user Extends Crud {

	# The table you want to perform the database actions on
	protected $table = 'user';

	# Primary Key of the table
	protected $pk  = 'id';

}

class useremail Extends Crud {

	# The table you want to perform the database actions on
	protected $table = 'useremail';

	# Primary Key of the table
	protected $pk  = 'id';

}

class emailmessage Extends Crud {

	# The table you want to perform the database actions on
	protected $table = 'emailmessage';

	# Primary Key of the table
	protected $pk  = 'id';

}

class headername Extends Crud {

	# The table you want to perform the database actions on
	protected $table = 'headername';

	# Primary Key of the table
	protected $pk  = 'id';

}

class header Extends Crud {

	# The table you want to perform the database actions on
	protected $table = 'header';

	# Primary Key of the table
	protected $pk  = 'id';
}

class attachment Extends Crud {

	# The table you want to perform the database actions on
	protected $table = 'attachment';

	# Primary Key of the table
	protected $pk  = 'id';
}
