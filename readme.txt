=== Social Kit by Melk ===
Contributors: melksedeque
Tags: social media, x, twitter, content generation, gutenberg
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Gera automaticamente título, texto e legenda para divulgar seus posts nas redes sociais, com contador de caracteres e painel no editor.

== Description ==

O Social Kit by Melk gera, ao salvar um post, todo o conteúdo necessário para divulgá-lo nas redes sociais: um rótulo e título para o card, um texto de apoio e a legenda completa já com o link — começando pelo X (Twitter), com arquitetura pronta para outras redes nas próximas versões.

Nada é travado em texto fixo: rótulos, CTAs, hashtags e stopwords usados na geração são configuráveis por filtros, então qualquer site WordPress pode adaptar aos próprios termos.

**Principais recursos**

* Geração automática ao salvar/publicar o post (título do card, texto do card, legenda e hashtags)
* Painel próprio no editor (Gutenberg) com contador de caracteres, botões de copiar e "Abrir no X"
* Edição manual trava a regeneração automática do campo, sem sobrescrever o que você já ajustou
* Integração opcional com o URL Shortener by Melk (usa o link curto automaticamente, se o outro plugin estiver ativo); sem ele, usa o link normal do post
* Escolha em quais tipos de conteúdo (post, página, CPTs) o recurso é ativado

**Roadmap**

* Suporte a mais redes (Instagram, Threads, Bluesky, LinkedIn)
* Vínculo de contas e publicação automática via API
* Integração com Canva para gerar a arte do card automaticamente

== Installation ==

1. Envie a pasta `social-kit-by-melk` para `/wp-content/plugins/` ou instale pelo repositório de plugins do WordPress.
2. Ative o plugin em **Plugins**.
3. Vá em **Configurações > Social Kit** e escolha os tipos de conteúdo onde o recurso deve ficar ativo.
4. Edite ou publique um post do tipo escolhido: o painel "Social Kit" aparece na barra lateral do editor com os campos já gerados.

== Frequently Asked Questions ==

= Preciso do URL Shortener by Melk para usar este plugin? =

Não. O link curto é usado automaticamente só se o URL Shortener by Melk estiver instalado e ativo; sem ele, o Social Kit usa o link normal do post.

= O plugin publica automaticamente nas redes sociais? =

Não, por enquanto. Esta versão gera os textos e disponibiliza um botão "Abrir no X" que já preenche a legenda; publicação automática via API está no roadmap.

= Dá para personalizar os rótulos, CTAs e hashtags? =

Sim, via os filtros `skbm_label_map`, `skbm_cta_map`, `skbm_cta_trim_list`, `skbm_stopwords` e `skbm_networks`.

= Preciso de um plugin de SEO (Yoast, Rank Math...) para usar este plugin? =

Não. Sem um plugin de SEO ativo, a primeira hashtag usa a primeira tag do post como aproximação. Se você já usa Yoast SEO, Rank Math ou outro plugin que defina a palavra-chave principal do post, o Social Kit reaproveita essa palavra-chave automaticamente — sem nenhuma configuração adicional, e sem exigir nenhum deles.

== Screenshots ==

1. Painel "Social Kit" no editor, com os campos gerados e contador de caracteres.
2. Tela de configurações, com os tipos de conteúdo habilitados.

== Changelog ==

= 1.0.0 =
* Primeira versão: geração por regras (sem IA), painel no editor, integração opcional com link curto.

== Upgrade Notice ==

= 1.0.0 =
Primeira versão pública.
