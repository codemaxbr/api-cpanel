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
    	$whm = $this->query('listaccts', ['api.version' => 1]);
        if(isset($whm->cpanelresult->error)){
            return FALSE;
        }else{
            return TRUE;
        }
    }

    public function terminateAccount($username = ''){
		if(empty($username)){
			throw new \Exception("'user' é obrigatório", 1);
		}

        $whm = $this->query('removeacct', ['user' => $username]);
        if(isset($whm->status)){
            return (object) [
                'status' => false,
                'error' => 'A conta "'.$username.'" não existe.',
                'whm' => $whm
            ];
        }else{
            return (object) [
                'status' => true,
                'message' => 'A conta "'.$username.'" foi removida.'
            ];
        }
	}

	public function unsuspendAccount($username = ''){
		if(empty($username)){
			throw new \Exception("Usuário é obrigatório", 1);
		}

        $whm = $this->query('unsuspendacct', ['user' => $username]);

        if(isset($whm->status)){
            return (object) [
                'status' => false,
                'verbose' => "A conta '{$username}' não existe."
            ];
        }else{
            return (object) [
                'status' => true,
                'verbose' => "A conta '{$username}' foi reativada."
            ];
        }
	}

	public function suspendAccount($username = ''){
		if(empty($username)){
			throw new \Exception("'username' é obrigatório", 400);
		}

		$args['user'] = $username;
        $whm = $this->query('suspendacct', $args);

        if(isset($whm->status)){
            return (object) [
                'status' => false,
                'error' => "A conta '{$username}' não existe."
            ];
        }else{
            return (object) [
                'status' => true,
                'message' => "A conta '{$username}' foi suspensa."
            ];
        }
	}

	public function modifyAccount($param = ''){
		if(empty($param) || !is_array($param)){
			throw new \Exception("Parâmetros inválidos", 1);
		}

		if(empty($param['user'])){
			throw new \Exception('O campo "Usuário" é obrigatório', 1);
		}

		$args['api.version'] = 1;
		$args['user'] = $param['user'];

		if(isset($param['bwlimit']) && !empty($param['bwlimit'])){
			$args['BWLIMIT'] = $param['bwlimit'];
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

        if(isset($whm->metadata) && $whm->metadata->result == 0){
            return (object) [
                'status' => false,
                'verbose' => 'A conta "'.$param['user'].'" não pode ser alterada.',
                'whm' => $whm
            ];
        }else{
            return (object) [
                'status' => true,
                'verbose' => 'A conta "'.$param['user'].'" foi alterada.',
            ];
        }
	}

    public function createAccount($param = null){
		if(empty($param) || !is_array($param))
		{
			throw new \Exception("Parâmetros inválidos.", 400);
		}

		if(!isset($param['user']) || empty($param['user']))
		{
			throw new \Exception("'user' é obrigatório.", 400);
		}

		if(!isset($param['domain']) || empty($param['domain']))
		{
			throw new \Exception("'domain' é obrigatório.", 400);
		}

        $senha = (isset($param['password'])) ? $param['password'] : gerar_senha(8);

		$args = [
			'api.version' => 1,
	        'username' => $param['user'],
	        'domain' => $param['domain'],
            'password' => $senha,
            'contactemail' => 'lucas.codemax@gmail.com',
            'plan' => (isset($param['plan'])) ? "{$this->whm_user}_". $param['plan'] : 'default',
	    ];

        $whm = $this->query('createacct', $args);

        if(isset($whm->metadata) && $whm->metadata->result == 0){
            return (object) [
                'status' => false,
                'error' => $whm
            ];
        }else{
            return (object) [
                'status' => true,
                'data' => (object) [
                    'domain' => $param['domain'],
                    'ip' => (isset($whm->data)) ? $whm->data->ip : null,
                    'username' => $param['user'],
                    'password' => $senha,
                    'ns1' => (isset($whm->data)) ? $whm->data->nameserver : null,
                    'ns2' => (isset($whm->data)) ? $whm->data->nameserver2 : null,
                    'package' => (isset($whm->data)) ? $whm->data->package : null,
                    'temp_url' => (isset($whm->data)) ? "http://{$whm->data->ip}/~{$param['user']}/" : null
                ]
            ];
        }
	}

	public function updatePassword($param = null){
		if(empty($param) || !is_array($param))
		{
			throw new \Exception("Parâmetros inválidos.", 1);
		}

		if(!isset($param['user']) || empty($param['user']))
		{
			throw new \Exception("O campo 'Usuário' é obrigatório.", 1);
		}

		if(!isset($param['password']) || empty($param['password']))
		{
			throw new \Exception("O campo 'Senha' é obrigatório.", 1);
		}

		$args = [
			'api.version' => 1,
	        'user' => $param['user'],
	        'password' => $param['password'],
	    ];

        $whm = $this->query('passwd', $args);

        if(isset($whm->metadata) && $whm->metadata->result == 0){
            return (object) [
                'status' => false,
                'error' => 'A senha que você escolheu é muito fraca.',
            ];
        }else{
            return (object) [
                'status' => true,
                'new_password' => $param['password'],
                'message' => 'A senha da conta "'.$param['user'].'" foi alterada.',
            ];
        }
	}

	public function limitBand($username = '', $bw = ''){
		if(empty($username)){
			throw new \Exception("Usuário é obrigatório", 400);
		}

		if(empty($bw)){
			throw new \Exception("Tráfego é obrigatório", 400);
		}

        $whm = $this->query('limitbw', ['api.version' => 1, 'user' => $username, 'bwlimit' => $bw]);

        if(isset($whm->metadata) && $whm->metadata->result == 0){
            return (object) [
                'status' => false,
                'message' => 'Você não tem acesso a conta "'.$username.'".'
            ];
        }else{
            return (object) [
                'status' => true,
                'message' => 'Limite de banda alterada.',
                'bwlimit' => $bw
            ];
        }
	}

	public function limitDisk($username = '', $disk = ''){
		if(empty($username)){
			throw new \Exception("'username' é obrigatório", 400);
		}

		if(empty($disk)){
			throw new \Exception("'disk' é obrigatório", 400);
		}

        $whm = $this->query('editquota', ['api.version' => 1, 'user' => $username, 'quota' => $disk]);

        if(isset($whm->metadata) && $whm->metadata->result == 0){
            return (object) [
                'status' => false,
                'error' => 'Você não tem acesso a conta "'.$username.'".'
            ];
        }else{
            return (object) [
                'status' => true,
                'message' => 'Espaço em Disco alterado.',
                'quota' => $disk
            ];
        }
	}

	public function summaryAccount($username = ''){
		if(empty($username)){
			throw new \Exception("'username' é obrigatório", 400);
		}

        $whm = $this->query('accountsummary', ['api.version' => 1, 'user' => $username]);

        if(isset($whm->metadata) && $whm->metadata->result == 0){
            return (object) [
                'status' => false,
                'verbose' => 'A conta "'.$username.'" não existe.'
            ];
        }else{
            return (object) [
                'status' => true,
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
        if(isset($whm->metadata) && $whm->metadata->result == 1){
            return $whm->data->pkg;
        }else{
            return (object) [
                'status' => false,
                'verbose' => 'Acesso Negado.'
            ];
        }
	}

	public function addPackage($param = null){
		if(empty($param) || !is_array($param))
		{
			throw new \Exception("Parâmetros inválidos.", 1);
		}

		if(!isset($param['name']) || empty($param['name']))
		{
			throw new \Exception("O campo 'Descricao' é obrigatório.", 1);
		}

		if(!isset($param['disk']) || empty($param['disk']))
		{
			throw new \Exception("O campo 'Espaço em Disco' é obrigatório.", 1);
		}

		if(!isset($param['bwlimit']) || empty($param['bwlimit']))
		{
			throw new \Exception("O campo 'Tráfego' é obrigatório.", 1);
		}

		if(isset($param['ip'])){
			$args['ip'] = $param['ip'];
		}

		if(isset($param['cgi'])){
			$args['cgi'] = $param['cgi'];
		}

		if(isset($param['frontpage'])){
			$args['frontpage'] = $param['frontpage'];
		}

		if(isset($param['theme'])){
			$args['cpmod'] = $param['theme'];
		}

		if(isset($param['maxpop'])){
			$args['maxpop'] = $param['maxpop'];
		}

		if(isset($param['maxsql'])){
			$args['maxsql'] = $param['maxsql'];
		}

		if(isset($param['maxaddon'])){
			$args['maxaddon'] = $param['maxaddon'];
		}

		if(isset($param['maxpark'])){
			$args['maxpark'] = $param['maxpark'];
		}

		if(isset($param['hasshell'])){
			$args['hasshell'] = $param['hasshell'];
		}

		$args['api.version'] = 1;
		$args['name'] = $param['name'];
		$args['quota'] = $param['disk'];
		$args['bwlimit'] = $param['bwlimit'];
		$args['language'] = 'pt_br';

        $whm = $this->query('addpkg', $args);

        if(isset($whm->metadata) && $whm->metadata->result == 1){
            return (object) [
                'status' => true,
                'message' => 'O plano "'.$param['name'].'" foi configurado.',
                'whm' => $whm
            ];
        }else{
            return (object) [
                'status' => false,
                'message' => 'O plano "'.$param['name'].'" já existe.'
            ];
        }
	}

	public function editPackage($param = null){
		if(empty($param) || !is_array($param))
		{
			throw new \Exception("Parâmetros inválidos.", 1);
		}

		if(!isset($param['name']) || empty($param['name']))
		{
			throw new \Exception("O campo 'Descricao' é obrigatório.", 1);
		}

		if(!isset($param['disk']) || empty($param['disk']))
		{
			throw new \Exception("O campo 'Espaço em Disco' é obrigatório.", 1);
		}

		if(!isset($param['bwlimit']) || empty($param['bwlimit']))
		{
			throw new \Exception("O campo 'Tráfego' é obrigatório.", 1);
		}

		if(isset($param['ip'])){
			$args['ip'] = $param['ip'];
		}

		if(isset($param['cgi'])){
			$args['cgi'] = $param['cgi'];
		}

		if(isset($param['frontpage'])){
			$args['frontpage'] = $param['frontpage'];
		}

		if(isset($param['theme'])){
			$args['cpmod'] = $param['theme'];
		}

		if(isset($param['maxpop'])){
			$args['maxpop'] = $param['maxpop'];
		}

		if(isset($param['maxsql'])){
			$args['maxsql'] = $param['maxsql'];
		}

		if(isset($param['maxaddon'])){
			$args['maxaddon'] = $param['maxaddon'];
		}

		if(isset($param['maxpark'])){
			$args['maxpark'] = $param['maxpark'];
		}

		if(isset($param['hasshell'])){
			$args['hasshell'] = $param['hasshell'];
		}

		$args['api.version'] = 1;
		$args['name'] = $param['name'];
		$args['quota'] = $param['disk'];
		$args['bwlimit'] = $param['bwlimit'];
		$args['language'] = 'pt_br';

        $whm = $this->query('editpkg', $args);

        if(isset($whm->metadata) && $whm->metadata->result == 1){
            return (object) [
                'status' => true,
                'verbose' => 'O plano "'.$param['name'].'" foi re-configurado.'
            ];
        }else{
            return (object) [
                'status' => false,
                'verbose' => 'Não foi possível fazer as alterações do plano "'.$param['name'].'".',
                'whm' => $whm
            ];
        }
	}

	public function deletePackage($pkg = ''){
		if(empty($pkg)){
			throw new \Exception("Plano é obrigatório", 1);
		}

        $whm = $this->query('killpkg', ['api.version' => 1, 'pkgname' => $this->whm_user.'_'.$pkg]);

        if(isset($whm->metadata) && $whm->metadata->result == 1){
            return (object) [
                'status' => true,
                'verbose' => 'O plano "'.$pkg.'" foi removido.'
            ];
        }else{
            return (object) [
                'status' => false,
                'verbose' => 'O plano "'.$pkg.'" não existe.'
            ];
        }
	}

	public function getPackage($pkg = ''){
		if(empty($pkg)){
			throw new \Exception("'pkg' é obrigatório", 400);
		}

        $whm = $this->query('getpkginfo', ['api.version' => 1, 'pkg' => $this->whm_user.'_'.$pkg]);

        if(isset($whm->metadata) && $whm->metadata->result == 1){

            return (object) [
                'status' => true,
                'disk' => $whm->data->pkg->QUOTA,
                'bwlimit' => $whm->data->pkg->BWLIMIT,
                'email_accounts' => ($whm->data->pkg->MAXPOP == NULL) ? 'unlimited' : $whm->data->pkg->MAXPOP,
                'domains_add' => ($whm->data->pkg->MAXADDON == NULL) ? 'unlimited' : $whm->data->pkg->MAXADDON,
                'theme' => $whm->data->pkg->CPMOD,
                'sqls' => ($whm->data->pkg->MAXSQL == NULL) ? 'unlimited' : $whm->data->pkg->MAXSQL,
                'domains_park' => ($whm->data->pkg->MAXPARK == NULL) ? 'unlimited' : $whm->data->pkg->MAXPARK,
                'acess_shell' => ($whm->data->pkg->HASSHELL == 1) ? 'yes' : 'no',
                'cgi' => ($whm->data->pkg->CGI == 1) ? 'yes' : 'no',
                'ip' => ($whm->data->pkg->IP == 1) ? 'yes' : 'no',
                'frontpage' => ($whm->data->pkg->FRONTPAGE == 1) ? 'yes' : 'no',
                'language' => $whm->data->pkg->LANG,
                'subdomains' => ($whm->data->pkg->MAXSUB == NULL) ? 'unlimited' : $whm->data->pkg->MAXSUB,
            ];

        }else{
            return (object) [
                'status' => false,
                'verbose' => 'O plano "'.$pkg.'" não existe.'
            ];
        }
	}

	public function changePackage($username = '', $pkg = ''){

		if(empty($username)){
			throw new \Exception('O campo "Usuário" é obrigatório', 1);
		}

		if(empty($pkg)){
			throw new \Exception('O campo "Plano" é obrigatório', 1);
		}

        $whm = $this->query('changepackage', ['api.version' => 1, 'user' => $username, 'pkg' => $this->whm_user.'_'.$pkg]);

        if(isset($whm->metadata) && $whm->metadata->result == 1){
            return (object) [
                'status' => true,
                'message' => 'Upgrade/Downgrade Completo para "'.$username.'"',
                'pkg' => $pkg
            ];
        }else{
            return (object) [
                'status' => false,
                'message' => 'O Plano "'.$pkg.'" ou Usuário "'.$username.'" não existe.',
                'whm' => $whm
            ];
        }
	}
}