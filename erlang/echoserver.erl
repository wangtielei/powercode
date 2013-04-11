-module(echoserver).
-compile(export_all).

start() -> 
	spawn(fun() -> loop([]) end).
	
rpc(Pid, Request) ->
	Pid ! {self(), Request}.
	
loop(X) ->
	receive 
		Any -> 
		io:format("Receive:~p~n", [Any]),
			loop(X)
	end.