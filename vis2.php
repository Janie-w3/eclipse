<!DOCTYPE html>
<!-- http://bl.ocks.org/mbostock/4600693 -->
<!-- http://bl.ocks.org/mbostock/4062045 -->
<!-- http://bl.ocks.org/mbostock/1095795 -->
<html>
<head>
<title>wave.io</title>	
<style>
	* { border: 0px; margin: 0px; }
	.node {
		stroke: #fff;
		stroke-width: 1.5px;
	}

	.gnode text {
	  pointer-events: none;
	  font: 10px sans-serif;
	  color: #333;
	}

	.link {
		fill: none;
		stroke: #bbb;
	}
	
	.left { float: left; }
	.right { float: right; }
	.container { width: 100%; }
	
	#header { background-color: #303133; }
	#status { padding: 0px 10px 0px 10px; }
	
	/* svg { border: 1px solid #999; } */
</style>
<!--<script src="https://d3js.org/d3.v4.min.js"></script> -->
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="js/d3.legend.js"></script>
</head>
<body>
	<div id="header"><img src="img/wave-logo.png"/></div>
<br/>
<div class="container">
	<div class="left"><svg id="nodearea" width="960" height="600"></svg></div>
	<div id="status"><!--Test--></div>
</div>
<!--
<button onClick="add_node();">Add Node</button>
<button onClick="remove_node();">Delete Node</button>
<button onClick="add_link();">Add Link</button>
<button onClick="remove_link();">Delete Link</button>
-->
<script>
var color = d3.scale.category10();
var svg = d3.select("#nodearea");
svg.append("g").attr("class", "glinks");
var width = svg.attr("width");
var height = svg.attr("height");

var gnodes;
var glinks;
var graph = {
	"nodes": [],
	"links": []
};

var force = d3.layout.force()
	.charge(-400)
	.linkDistance(120)
	.size([width,height]);

var drawGraph = function(graph) {
	force
		.nodes(graph.nodes)
		.links(graph.links)
		.start()
		
	//underlying g node
	gnodes = svg.selectAll("g.gnode").data(graph.nodes);
	var newnodes = gnodes.enter()
		.append('g')
		.classed('gnode', true)
		.call(force.drag);
	
	gnodes.exit().selectAll("circle").remove();
	gnodes.exit().selectAll("text").remove();
	gnodes.exit().remove();
	
	//underlying glinks
	glinks = (svg.selectAll("g.glinks")).selectAll(".link").data(graph.links);
	glinks.enter()
		.append("line")
		.attr("class", "link")
		.style("stroke-width", "2")
	
	glinks.exit().selectAll("circle").remove();
	glinks.exit().selectAll("text").remove();
	glinks.exit().remove();
	
	//var node = gnodes.append("circle")
	var node = newnodes.append("circle")
		.attr("class", "node")
		.attr("r", 10)
		.style("fill", function(d) { return color(d.role); })
	
	//text labels
	//var labels = gnodes.append("text")
	var labels = newnodes.append("text")
		.attr("dx", 12)
		.attr("dy", ".35em")
		.text(function(d) { return d.role + ": 0x" + d.id});
		
	force.on("tick", function() {
		// Update the links
		glinks.attr("x1", function(d) { return d.source.x; })
			.attr("y1", function(d) { return d.source.y; })
			.attr("x2", function(d) { return d.target.x; })
			.attr("y2", function(d) { return d.target.y; });

		// Translate the groups
		gnodes.attr("transform", function(d) { 
			return 'translate(' + [d.x, d.y] + ')'; 
		});
	});    
}

drawGraph(graph);
setInterval( check, 1000);

function check()
{
	d3.json("nodes.php", function(error, g) {
		if(error) throw error;
		
		var change = false;
		
		//check for nodes to add
		g.nodes.forEach(function(newnode) {
			found = false;
			
			graph.nodes.forEach(function(node) {
				if(node.id == newnode.id)
					found = true;
			});
			
			if(found == false)
			{
				change = true;
				graph.nodes.push(newnode);
			}
		});
		
		//check for nodes to remove
		var count = 0;
		graph.nodes.forEach(function(node) {
			found = false;
			
			g.nodes.forEach(function(newnode) {
				if(node.id == newnode.id)
					found = true;
			});
			
			if(found == false)
			{
				change = true;
				//alert("Removing node: " + node.id);
				graph.nodes.splice(count, 1);
			}
			count++;
		});
		
		//check for links to add
		g.links.forEach(function(newLink) {
			var s = get_node_index(newLink.source);
			var t = get_node_index(newLink.target);
			
			if((s != -1) && (t != -1))
			{
				var found = false;
				graph.links.forEach(function(link) {
					if((link.source.id == newLink.source) && (link.target.id == newLink.target))
						found = true;
				});
				
				if(found == false)
				{
					change = true;
					graph.links.push({"source": s, "target": t});
				}
			}	
		});
		
		//check for links to remove
		var count = 0;
		graph.links.forEach(function(oldLink) {
			var found = false;
			
			//alert(JSON.stringify(oldLink));
			
			g.links.forEach(function(newLink) {
				if((oldLink.source.id == newLink.source) && (oldLink.target.id == newLink.target))
					found = true;
				else
				{
					//alert("NO MATCH link: " + oldLink.source + " - " + oldLink.target + " with " + newLink.source + " - " + newLink.target);
				}
			});
			
			if(found == false && oldLink != undefined && oldLink.source.id != undefined)
			{
				change = true;
				graph.links.splice(count, 1);
			}
			
			count++;
		});
		
		if(change == true)
			drawGraph(graph);
	});
}

/*
 * Node index is the position in the data array or -1 if not found
 */
function get_node_index(id)
{
	var counter = 0;
	var index = -1;
	graph.nodes.forEach(function(node)
	{
		if(node.id == id)
			index=counter;
		counter++;
	});
	return index;
}


</script>
</body>
</html>
