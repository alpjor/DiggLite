<?php include 'header.tpl'; ?>
<body>
	<div id="container">
		
		<h1>Digglite</h1>

        <div class="about-api">
            <p><strong>An open-source, OAuth application to showcase Digg’s new API.</strong><br/>
            <a href="http://github.com/digg/DiggLite/downloads">Download</a> the DiggLite source code, or <a href="http://digg.com/api">View</a> the documentation.</p>
        </div>
        <div class="clear"></div>

<?php if (isset($user)) { ?>
        <a href="/logout.php" class="fbConnectButton"><span>Logout</span></a>
                <span id="users-name"><?php echo $user; ?></span>
<?php } else { ?>
        <a href="<?php echo $authURL; ?>" class="fbConnectButton"><span>Connect with Digg or Facebook</span></a>
<?php } ?>
		
		<form id="container-form" action="/" method="post">
            <input type="hidden" name="event" value="setContainer" />
			<span class="input">
				<select name="container">
					<option value="all">Select a feed category...</option>
					<option value="all">All</option>
                    <?php foreach ($containers as $container) {
                        $selected = '';
                        if ($container->short_name == $selectedContainer) {
                            $selected = ' selected="selected"';
                        }

                        echo "<option value=\"{$container->short_name}\" $selected>" . htmlentities($container->name, ENT_COMPAT, 'UTF-8') . "</option>\n";
                    } ?>
				</select>
			</span>
		</form>
		
		<div id="main-content">
			
			<h2>Popular In <?php echo $containerTitle ?></h2>
			
<?php
    foreach($stories as $story) {
        $dugg = false;
        if (isset($actions) && isset($actions[$story->id])) {
            if ($actions[$story->id] == 'dugg') {
                $dugg = true;
            } else {
                continue;
            }
        }

?>
			<div class="story">
                <?php if (isset($story->thumbnail)) {?>
				<a href="<?php echo $story->link; ?>"><img src="<?php echo $story->thumbnail->src; ?>" alt="Story Thumbnail" class="story-thumbnail"/></a>
                <?php } else {?>
				<a href="<?php echo $story->link; ?>"><img src="/img/thumbnail.gif" alt="No Thumbnail" class="story-thumbnail"/></a>

                <?php } ?>
				<h3><a href="<?php echo $story->link; ?>"><?php echo htmlentities($story->title, ENT_COMPAT, 'UTF-8'); ?></a></h3>
				<ul class="news-digg">
					<li class="digg-count">
					<a href="<?php echo $story->href; ?>"><strong class="diggs-strong"><?php echo $story->diggs; ?></strong> diggs </a>  </li>
					<li class="<?php echo ($dugg) ? 'dugg-it' : 'digg-it thumbs-up'; ?>" id="diglink-<?php echo $story->id; ?>">
                        <?php echo ($dugg) ? '<span>dugg!</span>' : '<a href="#">digg</a>'; ?>
                    </li>
				</ul>
				<ul class="options">
					<li class="comments"><a href="<?php echo $story->href; ?>#comments"><?php echo $story->comments; ?> comments</a></li>
					<li class="bury-link" id="bury-<?php echo $story->id; ?>"><a href="#">Bury</a></li>
				</ul>
				<div class="clear"></div>
                <div class="made-popular">Made popular <?php echo $story->since; ?></div>
			</div>
			<div class="clear"></div>
<?php } ?>			
            <a href="#" class="back-to-top"><img src="/img/back_to_top.gif" /></a>
        </div>
        <div class="clear"></div>
	</div>

</body>
</html>
