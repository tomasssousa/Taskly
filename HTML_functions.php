<?php function Draw_Head($title){ ?>
<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title><?=$title?></title>
        <link rel="stylesheet" href="        .css">
    </head>
    <body>
<?php } ?>

<!-- Principlamente funções que vão ser alteradas para cada tipo de estrutura -->
<!-- Poque eu não quero pensar na estrutura sozinho -->

<?php function Draw_Header(){?>
        <header>
            <h1><p>P is for Place holder</p></h1>
        </header>
<?php } ?>


<?php function Draw_Menu(){?>
        <nav id="menu">
            <p>P is for Place holder</p>
        </nav>
<?php } ?>


<?php function Draw_Aside(){?> <!-- Não sei quão util será -->
        <aside id="related">
            <p>P is for Place holder</p>
        </aside>
<?php } ?>


<?php function Draw_Section($id){?>  <!-- Não sei se as sections e os articles deveriam abrir e fechar em funções separadas para ser mais facil por coisas no meio-->
        <section id=<?$id?>>
            <p>P is for Place holder</p>
        </section>
<?php } ?>


<?php function Draw_Article(){?>
            <article>
                <p>P is for Place holder</p>
            </article>
<?php } ?>


<?php function Draw_Footer(){?>
        <footer>
            <p>P is for Place holder</p>
        </footer>
    </body>
</html>
<?php } ?>