<?php
/** CT11 (autenticação), CT12 (autorização por perfil e CSRF). */
class AuthServiceTest extends TesteBase
{
    private function servico()
    {
        return new AuthService(new UsuarioDAO());
    }

    public function setUp()
    {
        parent::setUp();
        $this->criarUsuario('vendedor', 'vendedor@teste.com', 'Senha@1234');
    }

    public function testCT11_loginValidoNormalizaOEmail()
    {
        $u = $this->servico()->autenticar('  VENDEDOR@Teste.com ', 'Senha@1234');
        $this->assertSame('vendedor', $u->getPerfil());
    }

    public function testCT11_senhaErradaEEmailInexistenteDaoMesmaMensagem()
    {
        $svc = $this->servico();
        $a = $this->assertLanca('AuthException', function () use ($svc) { $svc->autenticar('vendedor@teste.com', 'errada'); });
        $b = $this->assertLanca('AuthException', function () use ($svc) { $svc->autenticar('naoexiste@teste.com', 'qualquer'); });
        $this->assertSame(AuthService::MSG_INVALIDO, $a->getMessage());
        $this->assertSame($a->getMessage(), $b->getMessage(), 'não revela se o e-mail existe');
    }

    public function testCT11_camposVazios()
    {
        $svc = $this->servico();
        $this->assertLanca('AuthException', function () use ($svc) { $svc->autenticar('', ''); }, 'Informe o e-mail e a senha');
    }

    public function testCT11_contaBloqueadaAposCincoFalhasEliberadaApos15Minutos()
    {
        $svc = $this->servico();
        $t = strtotime('2026-11-10 12:00:00');
        for ($i = 1; $i <= 4; $i++) {
            $this->assertLanca('AuthException', function () use ($svc, $t) { $svc->autenticar('vendedor@teste.com', 'errada', $t); }, 'inválidos');
        }
        $this->assertLanca('AuthException', function () use ($svc, $t) { $svc->autenticar('vendedor@teste.com', 'errada', $t); }, 'bloqueado');
        // mesmo com a senha CERTA, continua bloqueada
        $this->assertLanca('AuthException', function () use ($svc, $t) { $svc->autenticar('vendedor@teste.com', 'Senha@1234', $t + 60); }, 'bloqueado');
        // depois de 15 minutos o acesso volta
        $u = $svc->autenticar('vendedor@teste.com', 'Senha@1234', $t + 16 * 60);
        $this->assertSame('vendedor@teste.com', $u->getEmail());
        $this->assertSame(0, (new UsuarioDAO())->buscarPorEmail('vendedor@teste.com')->getTentativasFalhas());
    }

    public function testCT11_loginCorretoZeraContadorDeFalhas()
    {
        $svc = $this->servico();
        $t = strtotime('2026-11-10 12:00:00');
        $this->assertLanca('AuthException', function () use ($svc, $t) { $svc->autenticar('vendedor@teste.com', 'errada', $t); });
        $this->assertSame(1, (new UsuarioDAO())->buscarPorEmail('vendedor@teste.com')->getTentativasFalhas());
        $svc->autenticar('vendedor@teste.com', 'Senha@1234', $t + 5);
        $this->assertSame(0, (new UsuarioDAO())->buscarPorEmail('vendedor@teste.com')->getTentativasFalhas());
    }

    public function testCT11_usuarioInativoNaoEntra()
    {
        (new UsuarioDAO())->definirAtivo(1, false);
        $svc = $this->servico();
        $this->assertLanca('AuthException', function () use ($svc) { $svc->autenticar('vendedor@teste.com', 'Senha@1234'); }, 'inválidos');
    }

    public function testCT11_senhaEhGravadaComHashENuncaEmTextoPuro()
    {
        $hash = $this->pdo->query('SELECT senha_hash FROM usuarios WHERE id = 1')->fetchColumn();
        $this->assertTrue($hash !== 'Senha@1234', 'não pode ser texto puro');
        $this->assertTrue(password_verify('Senha@1234', $hash), 'hash deve validar a senha');
    }

    public function testCT11_regrasDeSenhaEDeCadastroDeUsuario()
    {
        $this->assertLanca('InvalidArgumentException', function () { UsuarioFactory::validarSenha('123', '123'); }, 'pelo menos 8');
        $this->assertLanca('InvalidArgumentException', function () { UsuarioFactory::validarSenha('Senha@1234', 'Outra@1234'); }, 'não confere');
        $this->assertLanca('InvalidArgumentException', function () {
            UsuarioFactory::criarDeArray(array('nome' => 'X Y', 'email' => 'x@y.com', 'perfil' => 'hacker', 'senha' => 'Senha@1234', 'senha_confirmacao' => 'Senha@1234'));
        }, 'Perfil inválido');
        $this->assertLanca('InvalidArgumentException', function () {
            $this->criarUsuario('vendedor', 'vendedor@teste.com');
        }, 'Já existe um usuário');
    }
}

class AutorizacaoCsrfTest extends TesteBase
{
    private function logarComo($perfil)
    {
        $_SESSION['usuario'] = array('id' => 1, 'nome' => 'Teste', 'email' => 't@t.com', 'perfil' => $perfil);
    }

    public function testCT12_semLoginNenhumAcessoEhPermitido()
    {
        $this->assertFalse(Auth::podeAcessar('*'));
        $this->assertFalse(Router::permitido('produto/listar'));
        $this->assertTrue(Router::permitido('auth/login'), 'login é público');
    }

    public function testCT12_vendedorAcessaVendasMasNaoCadastraProdutoNemUsuarios()
    {
        $this->logarComo('vendedor');
        $this->assertTrue(Router::permitido('venda/novo'));
        $this->assertTrue(Router::permitido('produto/listar'), 'listar é para qualquer usuário logado');
        $this->assertFalse(Router::permitido('produto/criar'));
        $this->assertFalse(Router::permitido('relatorio/financeiro'));
        $this->assertFalse(Router::permitido('usuario/index'));
    }

    public function testCT12_analistaDeCadastroCadastraProdutosMasNaoVende()
    {
        $this->logarComo('cadastro');
        $this->assertTrue(Router::permitido('produto/criar'));
        $this->assertFalse(Router::permitido('venda/novo'));
    }

    public function testCT12_financeiroVeRelatorioEConfirmaPagamentoMasNaoCancela()
    {
        $this->logarComo('financeiro');
        $this->assertTrue(Router::permitido('relatorio/financeiro'));
        $this->assertTrue(Router::permitido('venda/confirmar'));
        $this->assertFalse(Router::permitido('venda/cancelar'));
    }

    public function testCT12_administradoraAcessaTudoERotaInexistenteEhNegada()
    {
        $this->logarComo('admin');
        $this->assertTrue(Router::permitido('usuario/index'));
        $this->assertTrue(Router::permitido('relatorio/financeiro'));
        $this->assertFalse(Router::permitido('rota/inexistente'), 'lista branca de rotas');
    }

    public function testCT12_menuEhFiltradoPeloPerfil()
    {
        $this->logarComo('estoquista');
        $rotas = array();
        foreach (Router::menu() as $item) {
            $rotas[] = $item[1];
        }
        $this->assertTrue(in_array('estoque/index', $rotas, true));
        $this->assertFalse(in_array('usuario/index', $rotas, true));
        $this->assertFalse(in_array('relatorio/financeiro', $rotas, true));
    }

    public function testCT12_acoesQueAlteramDadosSaoSomentePost()
    {
        $rotas = Router::rotas();
        foreach (array('produto/excluir', 'venda/confirmar', 'venda/cancelar', 'auth/logout', 'estoque/registrar', 'usuario/criar') as $r) {
            $this->assertSame('POST', $rotas[$r][3], $r . ' deve aceitar apenas POST');
        }
    }

    public function testCT12_tokenCsrfValidoPassaEInvalidoOuAusenteFalha()
    {
        $token = Csrf::token();
        $this->assertSame(64, strlen($token));
        $this->assertSame($token, Csrf::token(), 'mesmo token durante a sessão');
        $this->assertTrue(Csrf::validar($token));
        $this->assertFalse(Csrf::validar('token-forjado'));
        $this->assertFalse(Csrf::validar(''));
        $this->assertFalse(Csrf::validar(null));
        $this->assertContains('name="_csrf"', Csrf::campo());
    }

    public function testCT12_renovarInvalidaOTokenAntigo()
    {
        $antigo = Csrf::token();
        Csrf::renovar();
        $this->assertFalse(Csrf::validar($antigo));
        $this->assertTrue(Csrf::validar(Csrf::token()));
    }

    public function testCT12_mensagensFlashSaoExibidasUmaUnicaVez()
    {
        Flash::sucesso('Salvo!');
        $primeira = Flash::consumir();
        $this->assertSame(1, count($primeira));
        $this->assertSame('Salvo!', $primeira[0]['msg']);
        $this->assertSame(0, count(Flash::consumir()));
    }
}
