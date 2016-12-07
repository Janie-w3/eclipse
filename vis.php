<!DOCTYPE html>
<!-- http://bl.ocks.org/mbostock/4600693 -->
<!-- http://bl.ocks.org/mbostock/4062045 -->
<!-- http://bl.ocks.org/mbostock/1095795 -->
<html>
<head>
<title>wave.io</title>	
<style>
	.node {
		stroke: #fff;
		stroke-width: 1.5px;
	}

	.node text {
	  pointer-events: none;
	  font: 10px sans-serif;
	  color: #333;
	}

	.link {
		fill: none;
		stroke: #bbb;
        stroke-dasharray: 5, 5, 5, 5, 5, 5;
	}
	
	/* svg { border: 1px solid #999; } */
</style>
<!--<script src="https://d3js.org/d3.v4.min.js"></script>-->
<script src="https://d3js.org/d3.v3.min.js"></script>
<script src="js/d3.legend.js"></script>
</head>
<body>
<svg id="nodearea" width="960" height="600"></svg> <br/>

<button onClick="add_node();">Add Node</button>
<button onClick="remove_node();">Delete Node</button>
<button onClick="add_link();">Add Link</button>
<button onClick="remove_link();">Delete Link</button>

<script>
var color = d3.scale.category10();
var svg = d3.select("#nodearea");
var width = svg.attr("width");
var height = svg.attr("height");

var gnodes = [];
var nodes = [];
var links = [];

var force = d3.layout.force()
    .nodes(nodes)
    .links(links)
    .charge(-400)
    .linkDistance(120)
    .size([width, height])
    .on("tick", tick);

var node = svg.selectAll(".node");
var link = svg.selectAll(".link");
      
setInterval( refresh_source, 1000);

function start() {
  node = node.data(force.nodes(), function(d) { return d.id;});
  node.enter().append("circle").attr("class", function(d) { return "node " + d.id; }).attr("r", 8).attr("fill", function(d) { return color(d.role); });
  node.append("text").attr("dx", 12).attr("dy", ".35em").text("test")
  node.exit().remove();

  link = link.data(force.links(), function(d) { return d.source.id + "-" + d.target.id; });
  link.enter().insert("line", ".node").attr("class", "link");
  link.exit().remove();

  force.start();
}

function tick() {
  node.attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; })
      .attr("type", function(d) { return d.type })

  link.attr("x1", function(d) { return d.source.x; })
      .attr("y1", function(d) { return d.source.y; })
      .attr("x2", function(d) { return d.target.x; })
      .attr("y2", function(d) { return d.target.y; });
}

function add_node()
{
	var a = {id: nodes.length };
	nodes.push(a);

	start();
}

function remove_node()
{
	nodes.pop();
	start();
}

function add_link()
{
	var s = Math.floor(Math.random() * nodes.length);
	var t = Math.floor(Math.random() * nodes.length);
	links.push({"source": nodes[s], "target": nodes[t]});
	start();
}

function remove_link()
{
	var l = links.pop();
	//alert(JSON.stringify(l));
	start();
}

/*
 * Node index is the position in the data array or -1 if not found
 */
function get_node_index(id)
{
	var counter = 0;
	var index = -1;
	nodes.forEach(function(node)
	{
		if(node.id == id)
			index=counter;
		counter++;
	});
	return index;
}

function refresh_source()
{
	d3.json("nodes.php", function(error, graph) {
		if(error) throw error;

		//check for new nodes
		graph.nodes.forEach(function(newnode) {
			var found = false;
			
			nodes.forEach(function(node) {
				if(node.id == newnode.id)
					found = true;
			});
			
			if(found == false)
			{
				//alert(JSON.stringify(newnode));
				nodes.push(newnode);
			}
		});
		start();
		
		// check for nodes to remove
		var count = 0;
		nodes.forEach(function(oldnode) {
			var found = false;
			
			graph.nodes.forEach(function(node) {
				if(oldnode.id == node.id)
					found = true;
			});
			
			if(found == false)
				nodes.splice(count, 1);
			
			count++;
		});
		start();
		
		//check for new links
		graph.links.forEach(function(newLink) {
			
			var s = get_node_index(newLink.source);
			var t = get_node_index(newLink.target);
			
			if((s != -1) && (t != -1))
			{
				var found = false;
				links.forEach(function(link) {
					if((link.source.id == newLink.source) && (link.target.id == newLink.target))
						found = true;
				});
				
				if(found == false)
					links.push({"source": s, "target": t});
			}	
		});
		start();
		
		//check for links to remove
		var count = 0;
		links.forEach(function(oldLink) {
			var found = false;
			
			graph.links.forEach(function(link) {
				//alert("S: " + oldLink.source.id + " " + link.source + " T: " + oldLink.target.id + " " + link.target);
				if((oldLink.source.id == link.source) && (oldLink.target.id == link.target))
					found = true;
			});
			
			if(found == false)
				links.splice(count, 1);
			
			count++;
		});	
		start();
		
	});
}

</script>
</body>
</html>
