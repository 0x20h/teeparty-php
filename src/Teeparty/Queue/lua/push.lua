--[[
Push a task to the requsted channel and register
the task.

KEYS: 
    - the channel
    - task key.
ARGS: 
    - json-encoded task.
]]--
local channel, task_key, msg = KEYS[1], KEYS[2], ARGV[1]

redis.call('hmset', task_key, 'task', msg, 'channel', channel);
redis.call('lpush', channel, msg)

return 1
