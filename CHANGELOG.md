# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [1.0.0] - 2026-10-13

Primeira versão pública.

### ✨ Adicionado
- Geração automática de rótulo, título do card, texto do card e legenda do X ao salvar o post, usando regras determinísticas (sem IA e sem serviços externos), configuráveis via filtros `skbm_*`.
- Painel "Social Kit" no editor (Gutenberg) com contador de caracteres ponderado conforme as regras do X (URL = 23, emoji = 2, demais caracteres fora das faixas "leves" = 2), botões de copiar e "Abrir no X". O botão só abre uma aba no x.com quando clicado.
- Trava manual (`_skbm_locked`) para impedir que a regeneração automática sobrescreva edições feitas à mão; o painel indica a trava e permite destravar.
- Integração opcional com o URL Shortener by Melk (via `urlshbym_get_short_url_for_post()`), com fallback para o permalink quando o outro plugin não está ativo. Filtro `skbm_resolve_url` para trocar a URL usada na legenda.
- Tela de configurações (`Configurações > Social Kit`) para escolher os tipos de conteúdo habilitados, com o card "Outros plugins by Melk".
- Reaproveitamento da palavra-chave principal do Yoast SEO ou Rank Math (quando um deles está ativo) na primeira hashtag, via filtro `skbm_primary_keyword`; fallback para a primeira tag do post. Aviso discreto nas configurações sugerindo um plugin de SEO, sem exigir nenhum.
- `uninstall.php` e a opção **"Delete all plugin data"** na Danger Zone das configurações (desmarcada por padrão, para não perder dados ao reinstalar). Só tem efeito ao excluir o plugin; remove as opções `skbm_*` e os metas `_skbm_*` de todos os posts e é compatível com multisite.
- Rotina de upgrade (`skbm_db_version`, classe `Installer`): o hook de ativação não roda em atualizações pelo SVN, então os padrões e as migrações de dados são aplicados automaticamente quando a versão do banco muda.
- Tradução pt_BR (`languages/`), incluindo os textos do painel do editor (JS).
- Testes automatizados leves em `tests/` (`php tests/run.php`), sem Composer, fora do pacote do WordPress.org.

### 🐛 Correções (em relação ao MVP interno)
- A legenda e o texto do card preservam as quebras de linha (antes `sanitize_text_field` as transformava em espaço ao salvar).
- A legenda é regerada quando a URL muda (ex.: rascunho que ganha link curto ou permalink ao publicar); antes ficava com o `?p=ID` do rascunho. Rascunhos automáticos (`auto-draft`) não geram mais conteúdo.
- O contador de caracteres segue os pesos oficiais do X (ex.: `…` conta 2, `❤️` e sequências de emoji contam 2), com o mesmo resultado em PHP e JS.
- Quando o primeiro período do resumo é grande demais para os 280 caracteres ponderados, o gancho é encurtado por palavras (terminando com ponto final, sem reticências) em vez de descartado. O gancho só é descartado se nem 30 caracteres couberem; o link nunca é cortado.
- Metas registrados só nos tipos de conteúdo habilitados, com permissão `edit_post` por post; `source_hash` e `version` não são expostos na REST.
- O botão "Regenerar" não grava nada no banco (só devolve o resultado para o editor) e valida os parâmetros e o tipo de conteúdo.
- Painel: usa `wp.editor` (fallback `wp.editPost`), envia só o campo alterado, mostra erro e estado de carregamento, e o "Copiar tudo" não repete as hashtags.

### 🔧 Modificado
- Requer WordPress 6.2 ou superior e PHP 7.4; testado até o WordPress 7.1.
- Textos da interface, descrição do cabeçalho e `readme.txt` têm o inglês como idioma-fonte (padrão do WordPress.org); a tradução pt_BR fica em `languages/` e, depois, no translate.wordpress.org.
- Os textos do painel do editor passam a ser carregados pelo sistema de tradução (`wp_set_script_translations`).
- O card "Outros plugins by Melk" aponta para a página do URL Shortener no WordPress.org.
