<?php

/**
 * Description of lib_vindi
 *** Requisitos minimos: 
 * -PHP5.5
 * -Extensao CURL habilitada
 ***
 * @author Mateus Marques
 * Para mais informações, consulta a documentação oficial da plataforma Vindi http://atendimento.vindi.com.br/hc/pt-br
 */
class lib_vindi {
    
    // Chave da API Vindi
    private $apiKey = "";
    // Endereco da versao da API utilizada
    private $url = "https://app.vindi.com.br/api/v1/";
    
    private $errorRequest;
    private $errorRequestInfo;
    private $errorRequestMsg;

    /*
     * CRIAR CLIENTE
     * Este metodo retorna FALSE em caso de erro, e em caso de sucesso retorna todos os dados cadastrados, mais o ID do cliente na plataforma Vindi
     * Este metodo recebe um array, com as seguintes informacoes do cliente: 
     * $arrCliente = array(
            'name' => 'Fulano da Silva',                    // nome do cliente
            'email' => 'cliente@email.com',                 // email do cliente
            'registry_code' => '13814411761',               // documento de identificacao do cliente
            'code' => '#1',                                 // codigo opcional de referencia via API (nao pode ser repetido)
            'notes' => 'Sem notas',                         // observacao sobre o cliente (opcional) 
            'address' => array(                             // um array contendo as informacoes de endereco do cliente (opcional)
                'street' => 'Rua 10 de maio',
                'number' => '455',
                'additional_details' => 'Apartamento 201',
                'zipcode' => '22291368',
                'neighborhood' => 'Copacabana',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'country' => 'BR'
            )
        );
     */
    public function addCliente($arrCliente) {
        // Setando a url da requisicao
        $url = $this->url . 'customers';
        
        // converte o array em objeto JSON
        $data = json_encode($arrCliente, JSON_UNESCAPED_UNICODE);
        
        // faz a requisicai a API vindi, passando a url, os dados, e o metodo
        $resp = $this->requestVindi($url, $data, 'POST');
        
        return $this->returnRequest($resp); // chama o metodo re retorna da API
    }
    
    /*
     * CRIAR PERFIL DE PAGAMENTO
     * Este metodo retorna FALSE em caso de erro, e em caso de sucesso retorna os dados cadastrados
     * Este metodo recebe um array com as informacoes do cartao de creditom e o ID co cliente na plataforma vindi
     * $arrPerfil = array(
            'holder_name' => "MATEUS MARQUES",  // nome do proprietario do cartao
            'card_expiration' => "04/19",       // data de validade
            'card_number' => 4923993827951621,  // numero do cartao
            'card_cvv' => 671,                  // codigo do cartao
            'customer_id' => 91254              // ID do cliente na plataforma Vindi
        );
     */
    public function addPerfilPagamento($arrPerfil) {
        $url = $this->url . "payment_profiles"; // monta a url
        
        $data = json_encode($arrPerfil, JSON_UNESCAPED_UNICODE); // converte os dados para o ofrmato JSON
        
        $resp = $this->requestVindi($url, $data, "POST"); // chama o metodo que faz a requisicao
        
        return $this->returnRequest($resp); // chama o metodo re retorna da API
    }
    
    /*
     * CONSULTA METODOS DE PAGAMENTOS
     */
    public function getMetodosPagamento($param) {
        
    }
    
    /*
     * CRIAR ASSINATURA
     * É necessario que o cliente ja estaja cadastrado na plataforma, com seu respectivo perfi de pagamento
     * O metodo recebe como parametro um array de configuracoes da assinatura:
     * $arrAssinatura = array(
            'plan_id' => 1234, // Identificador do plano cadastrado na plataforma
            'customer_id' => 123,// Identificador do cliente cadastrado na plataforma
            'payment_method_code' =>, 'credit_card' // metodos de pagamentos aceitos
            'billing_trigger_day' => 10', // dia do pagamento
            'product_items' => array( 'product_id' => 14, 'product_id' => 15 ) // array com o Identificador de cada produto (ja cadastrado na plataforma) que estara presente nesta assinatura. 
        );
     */
    public function addAssinatura($arrAssinatura) {
        $url = $this->url . 'subscriptions';
        
        $data = json_encode($arrAssinatura, JSON_UNESCAPED_UNICODE);
        
        $resp = $this->requestVindi($url, $data, "POST");
        
        return $this->returnRequest($resp);
    }
    
    /*
     * Metodo de retorno da API
     * Este metodo recebe como parametro a resposta de uma requisicao feita na plataforma Vindi
     * E tem como saida, o seguinte array:
     *** em caso de erro
     * [
     *  'status',       // FALSE em caso de erro, e TRUE em caso de sucesso
     *  'errorArray',   // um array contendo todos os erros
     *  'errorMsg',     // Mensagem de erro geral
     *  'return'        // em caso de sucesso, o retorno serao os dados cadastrados na API Vindi
     * ]
     */
    private function returnRequest($resp){
        if(!$this->errorRequest){
            return array(
                'status' => FALSE,
                'errorsArray' => $resp->errors,
                'errorMsg' => $this->errorRequestMsg
            );
        }else{
            return array(
                'status' => TRUE,
                'return' => $resp
            );
        }
    }


    /*
     * Metodo que realiza as requisicoes a API vindi
     */
    private function requestVindi($url, $data = false, $method = FALSE){
        $curl = curl_init($url); //iniciando o curl
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //ignorar o certificado de seguranca
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //autoriza o recebimento de uma resposta do pagseguro
        
        if($data){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//informando os dados a serem transportados
        }
        
        curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey.':');//adiciona a chava da API junto a requisicao
        
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);//versao do http
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array("Content-Type: application/json; charset=UTF-8"));//seta o cabecalho para utf-8
        
        switch ($method) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_PUT, true);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        
        $resp = json_decode(curl_exec($curl));//executa a requisicao, e converte o JSON para objeto do PHP
        
        $this->errorRequestInfo = curl_getinfo($curl);//pega as informacoes de retorno da pagina
        
        $this->respHttp();//chama o metodo que verifica erros na requisicao
        
        curl_close($curl);//encerra o curl
        
        return $resp; //retorna os dados da api vindi
    }
    
    /*
     * Metodo para verificar a resposta http da API Vindi, e retorna uma resposta adequada
     */
    private function respHttp() {
        switch ($this->errorRequestInfo['info']['http_code']) {
            case '200':
                $this->errorRequest = TRUE;
                $this->errorRequestMsg = 'Requisição realizada com sucesso.';
                break;
            case '201':
                $this->errorRequest = TRUE;
                $this->errorRequestMsg = 'Criado com sucesso.';
                break;
            case '404':
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'Solicitação não encontrada.';
                break;
            case '406':
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'Formato enviado não é aceito.';
                break;
            case '422':
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'Parâmetros inválidos.';
                break;
            case '429':
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'O limite de requisições foi atingido.';
                break;
            case '400':
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'Sintaxe incorreta.';
                break;
            case '500':
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'Falha na plataforma.';
                break;
            default:
                $this->errorRequest = FALSE;
                $this->errorRequestMsg = 'A requisição não foi aceita.';
                break;
        }
    }
}
