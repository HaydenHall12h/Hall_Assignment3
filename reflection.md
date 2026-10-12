Why does searching use GET while creating a ticket uses POST?
Search is a read-only option, so it doesn't need write permissions from POST. All it needs to do is literally 
get information from the server.

We use POST for tickets because you're making a change on the server side of things. As POST has write 
capabilities, it's what is used for writing new Tickets to the server

Why is server-side validation still necessary if the HTML form performs validation?
That HTML validation can be bypassed. I think the example we used in class was Postman. Tools like those
allow you to circumvent the html form entirely and send stuff directly to the server. If there was no
validation server-side, then attackers could send a bunch of random data to fill up your databases and stuff.

What happens to an uploaded file before PHP moves it to the final upload directory?
The file gets held in a temporary directory. While in that directory it's given a random name. 
In addition to the name, the file's information is also pulled. Information like file type, original name,
size, etc. This stored information is useful for performing server-side validations before properly storing
the file within the final directory.

Where could you use a similar request-processing pattern in your capstone project?
I'm doing a social media style site for my project. I'll be able to use GET requests for searching for posts
and other information, like profiles potentially. I can use POST for making posts on a user feed.
The file processing stuff in this assignment seems like it will be particularly useful,
as I want to allow users to upload pictures like my reference site (X) allows for.