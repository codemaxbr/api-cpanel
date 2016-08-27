<?php 
namespace cPanel;

trait cPanelFunctions{

	/**
	 * Functions Accounts
	 */
	public function listAccounts(){
        
        $whm = $this->query('listaccts');

        if(isset($whm->cpanelresult->error)){
            return (object) [
                'status' => 0,
                'error' => 'auth_error',
                'verbose' => 'Usuário e senha / Chave de acesso incorreta.'
            ];
        }
        elseif(isset($whm->acct)){
            return $whm->acct;
        }
        else{
            return $whm;
        }
    }

    public function checkConnection(){
    	$result = $this->listAccounts();

    	if(isset($result['error'])){
    		return $result;
    	}else{
    		return TRUE;
    	}
    }

    public function terminateAccount($username = ''){
		if(empty($username)){
			throw new Exception("Usuário é obrigatório", 1);
		}

		$whm = $this->query('removeacct', ['user' => $username]);
		if(isset($whm->status)){
			return (object) [
                'status' => 0,
                'verbose' => 'A conta "'.$username.'" não existe.'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'A conta "'.$username.'" foi removida.'
            ];
		}
	}

	public function unsuspendAccount($username = ''){
		if(empty($username)){
			throw new Exception("Usuário é obrigatório", 1);
		}

		$whm = $this->query('unsuspendacct', ['user' => $username]);

		if(isset($whm->status)){
			return (object) [
                'status' => 0,
                'verbose' => 'A conta "'.$username.'" não existe.'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'A conta "'.$username.'" foi reativada.'
            ];
		}
	}

	public function suspendAccount($param = ''){
		if(empty($param)){
			throw new Exception("Usuário é obrigatório", 1);
		}

		$args['user'] = $param['user'];

		if(!empty($reason)){
			$args['reason'] = $param['reason'];
		}

		$whm = $this->query('suspendacct', $args);

		if(isset($whm->status)){
			return (object) [
                'status' => 0,
                'verbose' => 'A conta "'.$param['user'].'" não existe.'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'A conta "'.$param['user'].'" foi suspensa.'
            ];
		}
	}

	public function modifyAccount($param = ''){
		if(empty($param) || !is_array($param)){
			throw new Exception("Parâmetros inválidos", 1);
		}

		if(empty($param['user'])){
			throw new Exception('O campo "Usuário" é obrigatório', 1);
		}

		$args['api.version'] = 1;
		$args['user'] = $param['user'];

		if(isset($param['bwlimit']) && !empty($param['bwlimit'])){
			$args['DWLIMIT'] = $param['bwlimit'];
		}

		if(isset($param['email_contact']) && !empty($param['email_contact'])){
			$args['contactemail'] = $param['email_contact'];
		}

		if(isset($param['domain']) && !empty($param['domain'])){
			$args['DNS'] = $param['domain'];
		}

		if(isset($param['sqls']) && !empty($param['sqls'])){
			$args['MAXSQL'] = $param['sqls'];
		}

		if(isset($param['emails']) && !empty($param['emails'])){
			$args['MAXPOP'] = $param['emails'];
		}

		if(isset($param['new_user']) && !empty($param['new_user'])){
			$args['newuser'] = $param['new_user'];
		}

		if(isset($param['disk']) && !empty($param['disk'])){
			$args['QUOTA'] = $param['disk'];
		}

		$whm = $this->query('modifyacct', $args);

		if($whm->metadata->result == 0){
			return (object) [
                'status' => 0,
                'verbose' => 'A conta "'.$param['user'].'" não pode ser alterada.'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'A conta "'.$param['user'].'" foi alterada.',
            ];
		}
	}

    public function createAccount($param = null){
		if(empty($param) || !is_array($param))
		{
			throw new Exception("Parâmetros inválidos.", 1);
		}

		if(!isset($param['user']) || empty($param['user']))
		{
			throw new Exception("O campo 'Usuário' é obrigatório.", 1);
		}

		if(!isset($param['domain']) || empty($param['domain']))
		{
			throw new Exception("O campo 'Domínio' é obrigatório.", 1);
		}

		$args = [
			'api.version' => 1,
	        'username' => $param['user'],
	        'domain' => $param['domain'],
	    ];

	    $whm = $this->query('createacct', $args);

	    if($whm->metadata->result == 0){
			return (object) [
                'status' => 0,
                'verbose' => 'A conta "'.$param['user'].'" já existe / não pode ser criada.'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'A conta "'.$param['user'].'" foi criada.',
            ];
		}

		//return $whm;
	}

	public function updatePassword($param = null){
		if(empty($param) || !is_array($param))
		{
			throw new Exception("Parâmetros inválidos.", 1);
		}

		if(!isset($param['user']) || empty($param['user']))
		{
			throw new Exception("O campo 'Usuário' é obrigatório.", 1);
		}

		if(!isset($param['password']) || empty($param['password']))
		{
			throw new Exception("O campo 'Senha' é obrigatório.", 1);
		}

		$args = [
			'api.version' => 1,
	        'user' => $param['user'],
	        'password' => $param['password'],
	    ];

	    $whm = $this->query('passwd', $args);

	    if($whm->metadata->result == 0){
			return (object) [
                'status' => 0,
                'verbose' => 'A senha que você escolheu é muito fraca.'
            ];
		}else{
			return (object) [
                'status' => 1,
                'new_password' => $param['password'],
                'verbose' => 'A senha da conta "'.$param['user'].'" foi alterada.',
            ];
		}
	}

	public function limitBand($username = '', $bw = ''){
		if(empty($username)){
			throw new Exception("Usuário é obrigatório", 1);
		}

		if(empty($bw)){
			throw new Exception("Tráfego é obrigatório", 1);
		}

		$whm = $this->query('limitbw', ['api.version' => 1, 'user' => $username, 'bwlimit' => $bw]);

		if($whm->metadata->result == 0){
			return (object) [
                'status' => 0,
                'verbose' => 'Você não tem acesso a conta "'.$username.'".'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'Limite de banda alterada.'
            ];
		}
	}

	public function limitDisk($username = '', $disk = ''){
		if(empty($username)){
			throw new Exception("Usuário é obrigatório", 1);
		}

		if(empty($disk)){
			throw new Exception("Disco é obrigatório", 1);
		}

		$whm = $this->query('editquota', ['api.version' => 1, 'user' => $username, 'quota' => $disk]);
		
		if($whm->metadata->result == 0){
			return (object) [
                'status' => 0,
                'verbose' => 'Você não tem acesso a conta "'.$username.'".'
            ];
		}else{
			return (object) [
                'status' => 1,
                'verbose' => 'Espaço em Disco alterado.'
            ];
		}
	}

	public function summaryAccount($username = ''){
		if(empty($username)){
			throw new Exception("Usuário é obrigatório", 1);
		}

		$whm = $this->query('accountsummary', ['api.version' => 1, 'user' => $username]);

		if($whm->metadata->result == 0){
			return (object) [
                'status' => 0,
                'verbose' => 'A conta "'.$username.'" não existe.'
            ];
		}else{
			return (object) [
                'domain' => $whm->data->acct[0]->domain,
                'suspended' => $whm->data->acct[0]->suspended,
                'startdate_unix' => $whm->data->acct[0]->unix_startdate,
                'email_accounts' => $whm->data->acct[0]->maxpop,
                'sql_databases' => $whm->data->acct[0]->maxsql,
                'domains_add' => $whm->data->acct[0]->maxaddons,
                'subdomains' => $whm->data->acct[0]->maxsub,
                'user' => $whm->data->acct[0]->user,
                'plan' => $whm->data->acct[0]->plan,
                'diskused' => $whm->data->acct[0]->diskused,
                'disklimit' => $whm->data->acct[0]->disklimit,
            ];
		}
	}

	/**
	 * Functions Packages
	 */

	public function listPackages(){
		$whm = $this->query('listpkgs', ['api.version' => 1]);

		if(isset($whm->metadata->result) && $whm->metadata->result == 1){
			return $whm->data->pkg;
		}else{
			return (object) [
                'status' => 0,
                'verbose' => 'Acesso Negado.'
            ];
		}
	}
}