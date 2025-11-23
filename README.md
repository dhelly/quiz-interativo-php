# Quiz Interativo - Inútil.App

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

Um sistema de quiz interativo desenvolvido em PHP para estudo e prática de questões de Direito, com interface web moderna e recursos avançados de aprendizado adaptativo.

## 🎯 Demonstração

[![Ver Demonstração](https://img.shields.io/badge/🎮-Ver_Demo-2c3e50?style=for-the-badge)](https://quiz.inutil.app)

## ✨ Funcionalidades Principais

### 🎮 Quiz Interativo Avançado
- **Interface moderna** com design responsivo
- **Resposta imediata** - Processamento instantâneo ao clicar nas opções
- **Feedback visual** com cores para acertos/erros
- **Barra de progresso** em tempo real
- **Explicações integradas** - Visualização durante a resolução

### 📚 Sistema Inteligente de Revisão
- **Armazenamento automático** das questões erradas
- **Modo revisão dedicado** - Foca apenas nas dificuldades
- **Contadores em tempo real** de acertos e erros
- **Persistência por sessão** - Dados mantidos durante a navegação

### ⚙️ Painel de Administração Completo
- **Editor JSON integrado** - Edite questões diretamente no navegador
- **Upload de arquivos** com drag & drop
- **Visualização organizada** de todas as questões
- **Backup e restore** - Download e upload de dados
- **Validação em tempo real** da estrutura JSON

### 🎯 Modo Estudo Avançado
- **Atalhos de teclado** - Navegação rápida (1-4, Enter, E, R)
- **Análise visual** de respostas corretas e incorretas
- **Revelação de respostas** para estudo sem pressão
- **Explicações contextuais** durante a resolução

### 📊 Relatórios e Estatísticas
- **Resultados detalhados** com percentuais
- **Gráficos visuais** de desempenho
- **Recomendações personalizadas** de estudo
- **Histórico de desempenho** por sessão

## 🚀 Instalação Rápida

### Pré-requisitos
- PHP 7.4 ou superior
- Servidor web (Apache, Nginx, ou PHP built-in server)
- Navegador web moderno

### Passo a Passo

1. **Clone o repositório:**
```bash
git clone https://github.com/dhelly/quiz-interativo-php.git
cd quiz-interativo-php
```

2. **Configure o servidor web:**
```bash
# Usando servidor PHP embutido (recomendado para desenvolvimento)
php -S localhost:8000
```

3. **Acesse a aplicação:**
```
http://localhost:8000
```

### Estrutura de Arquivos
```
quiz-interativo-php/
├── index.php                 # Página principal do quiz
├── admin.php                # Painel de administração
├── salvar_errada.php        # API para gerenciar questões erradas
├── fim_quiz.php             # Tela de resultados finais
├── carregar_dados.php       # Funções para carregar dados do quiz
├── quiz_data.json           # Banco de questões (criado automaticamente)
└── templates/
    ├── quiz.php            # Template do quiz
    ├── admin_panel.php     # Template do painel admin
    └── fim_quiz.php        # Template dos resultados
```

## 📝 Estrutura das Questões

### Formato JSON
```json
[
  {
    "id": 1,
    "pergunta": "A inconstitucionalidade por omissão ocorre quando...",
    "resposta_correta": "Certo",
    "opcoes_disponiveis": ["Certo", "Errado"],
    "explicacao_feedback": "Explicação detalhada da resposta...",
    "topico": "Direito Constitucional",
    "nivel": "Intermediário"
  }
]
```

### Campos Obrigatórios:
- `id`: Identificador único (número)
- `pergunta`: Texto da questão
- `resposta_correta`: Resposta correta (deve coincidir com uma das opções)
- `opcoes_disponiveis`: Array com as opções de resposta
- `explicacao_feedback`: Explicação detalhada
- `topico`: Área do direito
- `nivel`: Dificuldade (Básico, Intermediário, Avançado)

## 🎮 Como Usar

### Para Estudantes
1. **Acesse o Quiz:** `http://localhost:8000`
2. **Responda questões:** Clique nas opções ou use teclas 1-4
3. **Veja feedback instantâneo:** Explicação aparece automaticamente
4. **Avance:** Clique em "Avançar" após responder
5. **Revise erradas:** No final, clique em "Revisar Questões Erradas"

### Para Administradores
1. **Acesse o Painel:** `http://localhost:8000/admin.php`
2. **Gerencie questões:** Use o editor JSON ou faça upload de arquivos
3. **Faça backup:** Download do banco de questões atual
4. **Restaure dados:** Volte para o conjunto padrão quando necessário

## ⌨️ Atalhos de Teclado

| Tecla | Ação |
|-------|------|
| `1-4` | Seleciona opções de resposta |
| `Enter` | Avança para próxima questão (após responder) |
| `E` | Mostra/oculta explicação (em desenvolvimento) |
| `R` | Mostra/oculta resposta correta (em desenvolvimento) |

## 🔧 Personalização

### Modificando o Tema
Edite as variáveis CSS no início de cada template:

```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #34495e;
    --accent-color: #3498db;
    --success-color: #27ae60;
    --error-color: #e74c3c;
}
```

### Adicionando Novos Tópicos
Modifique a estrutura JSON para incluir novos tópicos:

```json
"topico": "Novo Tópico",
"nivel": "Avançado"
```

## 🛠️ API Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| `GET` | `/index.php` | Página inicial do quiz |
| `GET` | `/admin.php` | Painel de administração |
| `POST` | `/salvar_errada.php` | Gerencia questões erradas na sessão |
| `GET` | `/fim_quiz.php` | Tela de resultados finais |

## 📊 Fluxo de Aprendizado

1. **Quiz Inicial** → Resposta imediata com feedback
2. **Identificação de Dificuldades** → Questões erradas são salvas automaticamente
3. **Revisão Dirigida** → Modo focado nas áreas problemáticas
4. **Consolidação** → Melhoria contínua do desempenho

## 🤝 Contribuindo

Contribuições são bem-vindas! Siga estos passos:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

### Guidelines para Contribuição
- Mantenha o código compatível com PHP 7.4+
- Siga o padrão de identidade visual existente
- Adicione comentários para novas funcionalidades complexas
- Teste em diferentes navegadores

## 🐛 Solução de Problemas

### Problemas Comuns

1. **Arquivo JSON não carrega:**
   - Verifique as permissões do diretório
   - Confirme que o JSON é válido

2. **Sessão não persiste:**
   - Verifique se o PHP tem suporte a sessões
   - Confirme que cookies estão habilitados

3. **Upload não funciona:**
   - Verifique `file_uploads` no php.ini
   - Confirme permissões de escrita

### Logs e Debug
Habilite a exibição de erros no PHP:
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## 📈 Próximas Funcionalidades

- [ ] **Timer para simulados** com controle de tempo
- [ ] **Modo aleatório** com questões misturadas

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para detalhes.

## 👥 Autores

- **Jaqueline Fernandes** - [@dhelly](https://github.com/dhelly)

## 🙏 Agradecimentos

- Comunidade PHP pela robustez e documentação
- Deepseek pelo empenho na confecção interativa de funcionalidades e correção de erros

---

## 🌟 Destaques Técnicos

### Arquitetura
- **PHP Vanilla** - Sem frameworks pesados
- **Sessões PHP** - Persistência de dados do usuário
- **JSON nativo** - Armazenamento simples e eficiente
- **CSS Variables** - Tema consistente e customizável

### Performance
- ⚡ **Carregamento rápido** - Interface otimizada
- 💾 **Baixo consumo** - Sem dependências externas
- 📱 **Responsivo** - Funciona em todos os dispositivos

### Pedagogia
- 🎯 **Aprendizado adaptativo** - Foco nas dificuldades
- 📚 **Revisão espaçada** - Consolidação de conhecimento
- 💡 **Feedback imediato** - Correção de conceitos

---

<div align="center">

**⭐ Se este projeto foi útil, deixe uma estrela no repositório!**

[![GitHub stars](https://img.shields.io/github/stars/dhelly/quiz-interativo-php?style=social)](https://github.com/dhelly/quiz-interativo-php)

**Desenvolvido com ❤️ para a comunidade jurídica**

</div>

## 📞 Suporte

Encontrou um problema? [Abra uma issue](https://github.com/dhelly/quiz-interativo-php/issues) no GitHub.

---

### 📊 Estatísticas do Projeto

- ✅ **+15 funcionalidades** implementadas
- ✅ **100% responsivo** - Mobile-first
- ✅ **Sistema de revisão** inteligente
- ✅ **Painel administrativo** completo
- ✅ **Atalhos de teclado** para produtividade
- ✅ **Persistência de dados** por sessão

### 🎯 Público-Alvo

- **Estudantes de concurso** - Preparação para OAB e concursos
- **Professores** - Criação de bancos de questões
- **Instituições de ensino** - Ferramenta de aprendizado
- **Autodidatas** - Estudo personalizado e direcionado
