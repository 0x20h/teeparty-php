--[[
store task results.

KEYS: 
 - result

ARGS:
 - json encoded task result
]]--

local result_key, task_key, result = KEYS[1], KEYS[2], ARGV[1]

local tries = redis.call('hget', task_key, 'tries')
redis.call('hset', result_key, tries, result)
