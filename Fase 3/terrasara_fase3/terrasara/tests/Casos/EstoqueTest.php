<?php
/** RN11 — movimentações de estoque auditadas (e correção do corte de texto UTF-8). */
class EstoqueTest extends TesteBase
{
    public function setUp()
    {
        parent::setUp();
        $this->prepararCenario();
    }

    public function testCT16_entradaSomaNoEstoqueERegistraAuditoria()
    {
        (new MovimentacaoDAO())->registrar(1, 1, 'entrada', 5, 'Reposição', '2026-11-09 08:00:00');
        $this->assertSame(15, $this->estoqueDe(1));
        $m = $this->pdo->query('SELECT * FROM movimentacoes_estoque ORDER BY id DESC LIMIT 1')->fetch();
        $this->assertSame('entrada', $m['tipo']);
        $this->assertSame(5, (int) $m['quantidade']);
        $this->assertSame(1, (int) $m['usuario_id']);
        $this->assertSame('Reposição', $m['motivo']);
        $this->assertSame('2026-11-09 08:00:00', $m['criado_em']);
    }

    public function testCT16_descarteDiminuiOEstoque()
    {
        (new MovimentacaoDAO())->registrar(1, 1, 'descarte', 4, 'Produto vencido');
        $this->assertSame(6, $this->estoqueDe(1));
    }

    public function testCT16_saidaMaiorQueOEstoqueNaoDeixaEstoqueNegativo()
    {
        $dao = new MovimentacaoDAO();
        $this->assertLanca('InvalidArgumentException', function () use ($dao) {
            $dao->registrar(1, 1, 'descarte', 11, 'teste');
        }, 'Estoque insuficiente');
        $this->assertSame(10, $this->estoqueDe(1));
        $this->assertSame(1, $this->contar('movimentacoes_estoque'), 'nada foi auditado');
    }

    public function testCT16_validacoesDeEntrada()
    {
        $dao = new MovimentacaoDAO();
        $this->assertLanca('InvalidArgumentException', function () use ($dao) { $dao->registrar(1, 1, 'entrada', 0); }, 'maior que zero');
        $this->assertLanca('InvalidArgumentException', function () use ($dao) { $dao->registrar(1, 1, 'entrada', 'x'); }, 'maior que zero');
        $this->assertLanca('InvalidArgumentException', function () use ($dao) { $dao->registrar(1, 1, 'roubo', 1); }, 'Tipo de movimentação inválido');
        $this->assertLanca('InvalidArgumentException', function () use ($dao) { $dao->registrar(999, 1, 'entrada', 1); }, 'Produto não encontrado');
    }

    public function testCT16_motivoLongoComAcentosNaoQuebraOUtf8()
    {
        // Bug encontrado na revisão: substr() cortava em 255 BYTES e partia letras acentuadas ao meio.
        (new MovimentacaoDAO())->registrar(1, 1, 'entrada', 1, str_repeat('ã', 300));
        $motivo = $this->pdo->query('SELECT motivo FROM movimentacoes_estoque ORDER BY id DESC LIMIT 1')->fetchColumn();
        $this->assertSame(1, preg_match('//u', $motivo), 'texto gravado deve ser UTF-8 válido');
        $this->assertSame(255, tamanho_texto($motivo), 'limite de 255 caracteres');
    }

    public function testCT16_listarRecentesTrazProdutoEUsuario()
    {
        $lista = (new MovimentacaoDAO())->listarRecentes(10);
        $this->assertSame(1, count($lista));
        $this->assertSame('Tomate orgânico', $lista[0]['produto']);
        $this->assertSame('Usuário Teste', $lista[0]['usuario']);
    }
}
